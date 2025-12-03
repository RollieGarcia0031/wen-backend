<?php

namespace App\Service;

use App\Database\Database;
use DateTime;
use Exception;
use InvalidArgumentException;
use PDOException;
use PDO;

class AppointmentService{

    /**
     * Insert a new appointment in the database, and upon
     * successful creation, a new notification will also
     * be inserted in the notif table
     *
     * @param array $params {
     *      @type int $availability_id
     *      @type string $messge
     *      @type string $header
     *      @type string $target_date
     *      @type int $student_user_id
     * }
     *
     * @return int the id of the saved appointment
     */
    public static function sendAppointment($params):int
    {
        $q1 = <<<SQL
            INSERT INTO appointments (
                availability_id,
                header,
                message,
                target_date,
                status,
                student_user_id
            )

            VALUES(
                :availability_id,
                :header,
                :message,
                :target_date,
                0,
                :student_user_id
            )
            
            RETURNING id
        SQL;

        $conn = Database::get()->connect();

        try {
            $conn->beginTransaction();

            // save the appointment in the
            // appointmnts table
            $stment = $conn->prepare($q1);
            $stment->execute($params);

            // get the id of the inserted appointment            
            $insertedAppointmentId = $stment->fetchColumn();

            // retrieve the user name of sender from database
            $stment = $conn->prepare(<<<SQL
                SELECT name FROM users
                WHERE id = :id
            SQL);

            $stment->execute(['id' => $params['student_user_id']]);

            $userName = $stment->fetch()['name'];

            //insert a new notifcation row
            $stment = $conn->prepare(<<<SQL
                INSERT INTO notifications (
                    message,
                    level
                )
                VALUES(
                    '$userName sent you an appointment request',
                    0
                )

                RETURNING id
            SQL);

            $stment->execute();

            // fetch the id of inserted notification
            $insertedNotifId = $stment->fetchColumn();


            // fetch the id of the target user professor
            $stment = $conn->prepare(<<<SQL
                SELECT user_id from availability
                WHERE id = ?
            SQL);

            $stment->execute([$params['availability_id']]);
            $targetUserId = $stment->fetch()['user_id'];

            // insert the user_notification row to connect
            // the created notification to the target user
            // of the sender
            $stment = $conn->prepare(<<<SQL
                INSERT INTO user_notifications (
                    status,
                    notification_id,
                    user_id
                ) VALUES (
                    0,
                    $insertedNotifId,
                    $targetUserId
                )
            SQL);

            $stment->execute();

            $conn->commit();

            return $insertedAppointmentId;
        } catch (PDOException $error){
            $conn->rollBack();
            throw $error;
        }
    }
    /**
     * Fetch paginated and filtered appointments for students or professors.
     *
     * Cursor Based Pagination:
     *  - cursor_date (DATE) and cursor_id (INT) define the last retrieved item.
     *  - When both are provided, we retrieve items strictly AFTER this composite value.
     *
     * Supports Filters:
     *  - status:     0 = pending, 1 = approved, 2 = declined
     *  - time_range: 'past', 'upcoming', 'today', 'all'
     *
     * @param array $params
     * @return array {
     *      items: array,
     *      next_cursor: ['cursor_id' => int, 'cursor_date' => string] | null
     * }
     */
    public static function fetchAppointments(array $params): array
    {
        $conn = Database::get()->connect();

        $isStudent   = isset($params['student_user_id']);
        $isProfessor = isset($params['professor_user_id']);

        if (!$isStudent && !$isProfessor) {
            throw new InvalidArgumentException("Expected student_user_id or professor_user_id.");
        }

        // Base SELECT statement
        $sql = <<<SQL
            SELECT
                apt.id,
                apt.status,
                apt.header,
                apt.target_date,
                av.day_of_week,
                av.start_time,
                av.end_time,
                u.name AS counterpart_name
            FROM appointments apt
            LEFT JOIN availability av ON apt.availability_id = av.id
        SQL;

        // Join correct user name based on caller role
        if ($isStudent) {
            $sql .= " LEFT JOIN users u ON av.user_id = u.id ";
        } else {
            $sql .= " LEFT JOIN users u ON apt.student_user_id = u.id ";
        }

        // WHERE Conditions
        $sql .= " WHERE 1=1 ";

        // Role-based visibility
        if ($isStudent) {
            $sql .= " AND apt.student_user_id = :student_user_id ";
        }

        if ($isProfessor) {
            $sql .= " AND av.user_id = :professor_user_id ";
            $sql .= " AND apt.visible_to_prof = TRUE ";
        }

        // Filter by status
        if (isset($params['status'])) {
            $sql .= " AND apt.status = :status ";
        }

        // Filter by time range
        if (!empty($params['time_range']) && $params['time_range'] !== 'all') {
            switch ($params['time_range']) {
                case 'past':
                    $sql .= " AND apt.target_date < CURRENT_DATE ";
                    break;
                case 'upcoming':
                    $sql .= " AND apt.target_date > CURRENT_DATE ";
                    break;
                case 'today':
                    $sql .= " AND apt.target_date = CURRENT_DATE ";
                    break;
            }
        }

        // Cursor Pagination (Only applied if both values exist)
        $useCursor = !empty($params['cursor_date']) && !empty($params['cursor_id']);

        if ($useCursor) {
            $sql .= "
                AND (
                    apt.target_date > :cursor_date
                    OR (apt.target_date = :cursor_date AND apt.id > :cursor_id)
                )
            ";
        }

        // ORDER & LIMIT (stable ordering for pagination)
        $sql .= " ORDER BY apt.target_date ASC, apt.id ASC ";
        $sql .= " LIMIT 20";

        // Execute SQL
        $stmt = $conn->prepare($sql);
        $stmt->execute(self::prepareSqlParams($params, $useCursor));

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Next Cursor
        $nextCursor = null;

        if (!empty($rows)) {
            $last = end($rows);
            $nextCursor = [
                'cursor_id'   => $last['id'],
                'cursor_date' => $last['target_date']
            ];
        }

        return [
            'items'       => $rows,
            'next_cursor' => $nextCursor
        ];
    }

    /**
     * Bind only the parameters that are actually used in SQL.
     */
    private static function prepareSqlParams(array $params, bool $useCursor): array
    {
        $bind = [];

        foreach (['student_user_id', 'professor_user_id', 'status'] as $key) {
            if (isset($params[$key])) {
                $bind[$key] = $params[$key];
            }
        }

        if ($useCursor) {
            $bind['cursor_date'] = $params['cursor_date'];
            $bind['cursor_id']   = $params['cursor_id'];
        }

        return $bind;
    }

    /**
     * Updates the status of an appointment
     * 
     * @param array $params {
     *      @type string professor_user_id  - id of the logged in professor, who recieved the appointment
     *      @type string id                 - id of the appointment to be updated
     *      @type int status             - new status of the appointment
     * }
     */
    public static function updateStatus($params):int
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare(<<<SQL
            UPDATE appointments AS apt
            SET status = :status
            FROM availability av
            WHERE
                apt.availability_id = av.id
                AND apt.id = :id
                AND av.user_id = :professor_user_id
                AND apt.status = 0
        SQL);

        $stment->execute($params);

        $rowCount = $stment->rowCount();

        return $rowCount;
    }

    /**
     * Sets the status of an appointment to "approved" (status = 1)
     *  - if sucessful a notification is also created for the student
     * 
     * @param array $params {
     *    @type string id                - id of the appointment to be approved
     *    @type string professor_user_id - id of the professor approving the appointment
     * }
     */
    public static function approveAppointment($params):int
    {
        $conn = Database::get()->connect();

        try {
            $conn->beginTransaction();

            // update the appointment status
            $stment = $conn->prepare(<<<SQL
                UPDATE appointments a
                SET status = 1
                FROM availability av
                WHERE (
                    a.id = :id
                    AND a.status = 0
                    AND av.user_id = :professor_user_id
                    AND a.availability_id = av.id 
                )
            SQL);

            // bind params and execute
            $stment->execute([
                'id' => $params['id'],
                'professor_user_id' => $params['professor_user_id']
            ]);

            // get number of affected rows
            $affectedRows = $stment->rowCount();

            if ($affectedRows == 0)
                throw new Exception("No appointment updated");

            // get user name of professor
            $stment = $conn->prepare(<<<SQL
                SELECT name FROM users
                WHERE id = :id
            SQL);
            $stment->execute(['id' => $params['professor_user_id']]);

            $profName = $stment->fetch()['name'];
            $notifMessage = "Your appointment for $profName has been approved";

            // insert a new notification for the student
            $stment = $conn->prepare(<<<SQL
                INSERT INTO notifications (
                    message,
                    level
                )
                VALUES(
                    '$notifMessage',
                    0
                )

                RETURNING id
            SQL);

            $stment->execute();

            $insertedNotifId = $stment->fetchColumn();

            // fetch the student user id from the appointment
            $stment = $conn->prepare(<<<SQL
                SELECT student_user_id FROM appointments
                WHERE id = :id
            SQL);
            $stment->execute(['id' => $params['id']]);
            $student_user_id = $stment->fetch()['student_user_id'];

            // link the notification to the student user
            $stment = $conn->prepare(<<<SQL
                INSERT INTO user_notifications (
                    status,
                    notification_id,
                    user_id
                ) VALUES (
                    0,
                    $insertedNotifId,
                    :student_user_id
                )
            SQL);

            $stment->execute(['student_user_id' => $student_user_id]);

            $conn->commit();

            return $affectedRows;

        } catch (PDOException $error){
            $conn->rollBack();
            throw $error;
        } catch (Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }
    /**
     * Deletes a specific appointment, based on a given
     * id
     * 
     * @param array $params {
     *      @type string student_user_id
     *      @type string id
     * }
     * @param string $student_name name of the student
     */
    public static function delete(array $params, string $student_name):int
    {
        $conn = Database::get()->connect();

        try {
            $conn->beginTransaction();

            // determine the status and target_date of appointment
            $stment = $conn->prepare(<<<SQL
                SELECT status, target_date FROM appointments
                WHERE id = ?
            SQL);
            $stment->execute([ $params['id'] ]);

            $result = $stment->fetch();

            if (!$result) throw new Exception("Appointment not found");
            $status = $result['status'];
            $target_date = $result['target_date'];
            $current_date = new DateTime();

            if (
                $status < 2
                && $target_date < $current_date // the appointment haven't been done
            ) {
                // create a notification for the professor

                // get user id of proffesor
                $stment = $conn->prepare(<<<SQL
                    SELECT
                        av.user_id AS professor_id
                    FROM appointments apt
                    JOIN availability av
                        ON av.id = apt.availability_id
                    WHERE apt.id = ?
                SQL);
                $stment->execute([$params['id']]);
                $professor_user_id = $stment->fetch()['professor_id'];

                // insert a new notifcation
                $message = "$student_name cancelled his appointment";
                $stment = $conn->prepare(<<<SQL
                    INSERT INTO notifications (
                        message,
                        level
                    ) VALUES (
                        '$message',
                        0
                    )

                    RETURNING id
                SQL);
                $stment->execute();
                $new_notfication_id = $stment->fetchColumn();

                // insert into user_notification table
                // to notify the professor
                $stment = $conn->prepare(<<<SQL
                    INSERT INTO user_notifications
                    ( user_id, status, notification_id )
                    VALUES (?, 0, ?)
                SQL);
                $stment->execute([$professor_user_id, $new_notfication_id]);
            }

            // delete the appointment
            $stment = $conn->prepare(<<<SQL
                DELETE FROM appointments
                WHERE
                    student_user_id = :student_user_id
                    AND id = :id
            SQL);
    
            $stment->execute($params);
    
            $rowCount = $stment->rowCount();
            
            $conn->commit();
        } catch (PDOException $error){
            $conn->rollBack();
            throw $error;
        } catch (Exception $error){
            $conn->rollBack();
            throw $error;
        }

        return $rowCount;
    }

    /**
     * Updates the message of an appointment
     * 
     * @param array $params {
     *      @type string student_user_id
     *      @type string id
     *      @type string message
     * }
     */
    public static function updateMessage(array $params):int
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare(<<<SQL
            UPDATE appointments
            SET message = :message
            WHERE
                id = :id
                AND student_user_id = :student_user_id
        SQL);

        $stment->execute($params);

        $rowCount = $stment->rowCount();

        return $rowCount;
    }

    /**
     * Declines a specific appointment, based on a given
     * id
     * 
     * creates a notification for the student upon success
     * 
     * @param array $params {
     *      @type string professor_user_id - id of the logged in professor, who recieved the appointment
     *      @type string id                - id of the appointment to be declined
     * }
     */
    public static function declineAppointment($params):int
    {
        $conn = Database::get()->connect();
        try {
            $conn->beginTransaction();

            // update the appointment status
            $stment = $conn->prepare(<<<SQL
                UPDATE appointments AS apt
                SET status = 2
                FROM availability av
                WHERE
                    apt.availability_id = av.id
                    AND apt.id = :id
                    AND av.user_id = :professor_user_id
                    AND apt.status = 0
            SQL);

            $stment->execute($params);

            $affectedRows = $stment->rowCount();

            if ($affectedRows == 0)
                throw new Exception("No appointment updated");

            // get user name of professor
            $stment = $conn->prepare(<<<SQL
                SELECT name FROM users
                WHERE id = :id
            SQL);
            $stment->execute(['id' => $params['professor_user_id']]);

            $profName = $stment->fetch()['name'];
            $notifMessage = "Your appointment for $profName has been declined";

            // insert a new notification for the student
            $stment = $conn->prepare(<<<SQL
                INSERT INTO notifications (
                    message,
                    level
                )
                VALUES(
                    '$notifMessage',
                    0
                )

                RETURNING id
            SQL);

            $stment->execute();

            $insertedNotifId = $stment->fetchColumn();

            // fetch the student user id from the appointment
            $stment = $conn->prepare(<<<SQL
                SELECT student_user_id FROM appointments
                WHERE id = :id
            SQL);
            $stment->execute(['id' => $params['id']]);
            $student_user_id = $stment->fetch()['student_user_id'];

            // link the notification to the student user
            $stment = $conn->prepare(<<<SQL
                INSERT INTO user_notifications (
                    status,
                    notification_id,
                    user_id
                ) VALUES (
                    0,
                    $insertedNotifId,
                    :student_user_id
                )
            SQL);

            $stment->execute(['student_user_id' => $student_user_id]);

            $conn->commit();

            return $affectedRows;

        } catch (PDOException $error){
            $conn->rollBack();
            throw $error;
        } catch (Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * Updates multiple appointments in one request
     * It sets the visible_to_prof column to FALSE
     *
     * @param array $params {
     *      @type array ids                - the ids of target appointments
     *      @type string professor_user_id - the professor's user id
     * }
     *
     * @return int ammount of rows affected
     */
    public static function hideMultiple(array $params):int
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            UPDATE appointments apt
            SET visible_to_prof = FALSE
            FROM availability av
            WHERE
                apt.id = :id
                AND av.id = apt.availability_id
                AND av.user_id = :professor_user_id
        SQL;

        try {
            $conn->beginTransaction();

            $affectedRows = 0;
            
            foreach($params['ids'] as $id){

                $newParam = [
                    "id" => $id,
                    "professor_user_id" => $params["professor_user_id"]
                ];

                $stment = $conn->prepare($q);
                $stment->execute($newParam);
                
                $affectedRows += $stment->rowCount();
            }

            $conn->commit();

            return $affectedRows;

        } catch (PDOException $error) {
            $conn->rollBack();
            throw $error;
        } catch (Exception $error) {
            $conn->rollBack();
            throw $error;
        }
    }

    /**
     * Selects pending and approved appointment for the current day
     * Fetch implepements cursor-page-pagination
     *
     * @param array $params {
     *      @type string user_id   - user id of professor
     *      @type int    cursor_id - reference as starting index for pagination
     *      @type string cursor_time - reference time for pagination
     * }
     */
    public static function getCurrentRecivedAppointments(array $params): array
    {
        $conn = Database::get()->connect();

        $limit = 10;
        $cursor_id = $params['cursor_id'] ?? 0;
        $cursor_time = $params['cursor_time'] ?? null;
        $user_id = $params['user_id'];

        // FIRST PAGE (no cursor yet)
        if ($cursor_id == 0 || $cursor_time === null) {

            $q = <<<SQL
                SELECT
                    apt.id,
                    apt.message,
                    apt.status,
                    av.start_time,
                    av.end_time,
                    u.name
                FROM appointments apt
                JOIN availability av
                    ON av.id = apt.availability_id
                JOIN users u
                    ON u.id = apt.student_user_id
                WHERE
                    apt.target_date = CURRENT_DATE
                    AND apt.status < 2
                    AND av.user_id = :user_id
                ORDER BY av.start_time ASC, apt.id ASC
                LIMIT :limit
            SQL;

            $stment = $conn->prepare($q);
            $stment->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stment->bindValue(':limit', $limit, PDO::PARAM_INT);

        } else {

            // NEXT PAGES
            $q = <<<SQL
                SELECT
                    apt.id,
                    apt.message,
                    apt.status,
                    av.start_time,
                    av.end_time,
                    u.name
                FROM appointments apt
                JOIN availability av
                    ON av.id = apt.availability_id
                JOIN users u
                    ON u.id = apt.student_user_id
                WHERE
                    apt.target_date = CURRENT_DATE
                    AND apt.status < 2
                    AND av.user_id = :user_id
                    AND (
                        av.start_time > :cursor_time
                        OR (av.start_time = :cursor_time AND apt.id > :cursor_id)
                    )
                ORDER BY av.start_time ASC, apt.id ASC
                LIMIT :limit
            SQL;

            $stment = $conn->prepare($q);
            $stment->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stment->bindValue(':cursor_time', $cursor_time);       // TIME
            $stment->bindValue(':cursor_id', $cursor_id, PDO::PARAM_INT);
            $stment->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stment->execute();
        $result = $stment->fetchAll();

        // Build next cursor
        if ($result) {
            $last = end($result);
            $next_cursor_id = $last['id'];
            $next_cursor_time = $last['start_time'];
        } else {
            $next_cursor_id = null;
            $next_cursor_time = null;
        }

        return [
            "data" => $result,
            "next_cursor_id" => $next_cursor_id,
            "next_cursor_time" => $next_cursor_time
        ];
    }

    /**
     * Retrieves the pending and approved appointments for the current day
     * that are sent by the students
     *
     * @param array $params {
     *    @type string user_id - user id of the logged student
     *    @type int    cursor_id       - id of appointment as reference for pagination
     *    @type string cursor_time    - time of appointment as reference for pagination
     * }
     */
    public static function getCurrentSentAppointments(array $params): array
    {
        $conn = Database::get()->connect();

        $limit = 10;

        $cursor_id = $params['cursor_id'] ?? 0;
        $cursor_time = $params['cursor_time'] ?? null;
        $student_user_id = $params['user_id'];

        if ($cursor_id == 0 || $cursor_time === null) {

            // First page
            $q = <<<SQL
                SELECT
                    apt.id,
                    apt.status,
                    apt.message,
                    av.start_time,
                    av.end_time,
                    u.name
                FROM appointments apt
                JOIN availability av ON av.id = apt.availability_id
                JOIN users u ON u.id = av.user_id
                WHERE
                    apt.target_date = CURRENT_DATE
                    AND apt.student_user_id = :student_user_id
                    AND apt.status < 2
                ORDER BY av.start_time ASC, apt.id ASC
                LIMIT :limit
            SQL;

            $stm = $conn->prepare($q);
            $stm->bindValue(":student_user_id", $student_user_id, PDO::PARAM_INT);
            $stm->bindValue(":limit", $limit, PDO::PARAM_INT);

        } else {

            // Next pages
            $sql = <<<SQL
                SELECT
                    apt.id,
                    apt.status,
                    apt.message,
                    av.start_time,
                    av.end_time,
                    u.name
                FROM appointments apt
                JOIN availability av ON av.id = apt.availability_id
                JOIN users u ON u.id = av.user_id
                WHERE
                    apt.target_date = CURRENT_DATE
                    AND apt.student_user_id = :student_user_id
                    AND apt.status < 2
                    AND (
                        av.start_time > :cursor_time
                        OR (av.start_time = :cursor_time AND apt.id > :cursor_id)
                    )
                ORDER BY av.start_time ASC, apt.id ASC
                LIMIT :limit
            SQL;

            $stm = $conn->prepare($sql);
            $stm->bindValue(":student_user_id", $student_user_id, PDO::PARAM_INT);
            $stm->bindValue(":cursor_time", $cursor_time);
            $stm->bindValue(":cursor_id", $cursor_id, PDO::PARAM_INT);
            $stm->bindValue(":limit", $limit, PDO::PARAM_INT);
        }

        $stm->execute();
        $result = $stm->fetchAll();

        if ($result) {
            $last = end($result);
            $next_cursor_id   = $last["id"];
            $next_cursor_time = $last["start_time"];
        } else {
            $next_cursor_id   = null;
            $next_cursor_time = null;
        }

        return [
            "data"             => $result,
            "next_cursor_id"   => $next_cursor_id,
            "next_cursor_time" => $next_cursor_time
        ];
    }

    /**
     * Returns the count of appointments today grouped by different status
     *
     * @param array $params {
     *      @type string user_id - id of the student owning appointments
     * }
     */
    public static function getStudentsAppointmentCountToday(array $params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                status,
                COUNT(*)
            FROM appointments
            WHERE
                student_user_id = :user_id
                AND target_date = CURRENT_DATE
            GROUP BY status
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($params);

        $result = $stment->fetchAll();

        return $result;
    }

    /**
     * Returns the count of appointments reviced by the proffesor
     * for current day
     *
     * @param array $param {
     *      @type string user_id - id of proffesor who recieves appointments
     * }
     */
    public static function getProfAppointmentCountToday(array $param):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                apt.status,
                COUNT(apt.*)
            FROM appointments apt
            JOIN availability av
                ON apt.availability_id = av.id
            WHERE
                av.user_id = :user_id
                AND apt.target_date = CURRENT_DATE
            GROUP BY apt.status
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($param);

        $result = $stment->fetchAll();

        return $result;
    }

    /**
     * Returns the count of appointments of given student user id
     * for tomorrow
     *
     * @param array $params {
     *      @type string user_id - user id of student as owner of appointments
     * }
     */
    public static function getStudentAppointmentCountTomorrow(array $params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                status,
                COUNT(*)
            FROM appointments
            WHERE
                student_user_id = :user_id
                AND target_date >= CURRENT_DATE + INTERVAL '1 day'
                AND target_date < CURRENT_DATE + INTERVAL '2 day'
            GROUP BY status
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($params);

        $result = $stment->fetchAll();

        return $result;
    }

    /**
     * Returns the count of appointments of professor for the tomorrow
     *
     * @param array $params {
     *      @type string user_id - user id of professor
     * }
     */
    public static function getProfAppointmentCountTomorrow(array $params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                apt.status,
                COUNT(apt.*)
            FROM appointments apt
            JOIN availability av
                ON av.id = apt.availability_id
            WHERE
                av.user_id = :user_id
                AND target_date >= CURRENT_DATE + INTERVAL '1 day'
                AND target_date < CURRENT_DATE + INTERVAL '2 day'
            GROUP BY apt.status
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($params);

        $result = $stment->fetchAll();

        return $result;
    }

    /**
     * Returns the count of appointment sent by the students with a target
     * date from current day to upcoming sunday
     *
     * @param array $param {
     *      @type string user_id - user id of student
     * }
     */
    public static function getStudentAppointmentCountWeekly(array $param):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                status,
                COUNT(*)
            FROM appointments
            WHERE
                student_user_id = :user_id
                AND
                    target_date BETWEEN CURRENT_DATE
                    AND (
                        CURRENT_DATE
                        + INTERVAL '1 day'
                        * (7 - EXTRACT(DOW FROM CURRENT_DATE))
                    )
            GROUP BY status
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($param);

        $result = $stment->fetchAll();
        return $result;
    }

    /**
     * Returns the count of appointments recived by the professor
     * with a target_date from today to upcoming sunday
     *
     * @param array $param {
     *      @type string user_id - user id of logged professor
     * }
     */
    public static function getProfApointmentCountWeekly(array $param):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                apt.status,
                COUNT(apt.*)
            FROM appointments apt
            JOIN availability av
                ON av.id = apt.availability_id
            WHERE
                av.user_id = :user_id
                AND
                    target_date BETWEEN CURRENT_DATE
                    AND (
                        CURRENT_DATE
                        + INTERVAL '1 day'
                        * (7 - EXTRACT(DOW FROM CURRENT_DATE))
                    )
            GROUP BY apt.status
        SQL;

        $stment = $conn->prepare($q);
        $stment->execute($param);

        $result = $stment->fetchAll();
        return $result;
    }
}
