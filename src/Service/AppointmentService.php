<?php

namespace App\Service;

use App\Database\Database;
use PDOException;

class AppointmentService{

    /**
     * Insert a new appointment in the database, and upon
     * successful creation, a new notification will also
     * be inserted in the notif table
     *
     * @param array $params {
     *      @type int $availability_id
     *      @type string $messge
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
                message,
                target_date,
                status,
                student_user_id
            )

            VALUES(
                :availability_id,
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
                    level,
                    state
                )
                VALUES(
                    '$userName sent you an appointment request',
                    0,
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
     * Retrieves a list of appointments sent by a student
     * 
     * @param array $params {
     *      @type string student_user_id
     *      @type string status
     * }
     */
    public static function getAllSentAppointments(array $params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                apt.id,
                apt.status,
                apt.message,
                apt.target_date,
                av.day_of_week,
                av.start_time,
                av.end_time,
                u.name
            FROM appointments apt
            LEFT JOIN availability av ON apt.availability_id = av.id
            LEFT JOIN users u ON av.user_id = u.id
            WHERE apt.student_user_id = :student_user_id
            ORDER BY apt.target_date ASC
        SQL;
        
        if (isset($params['status'])) {            
            $q .= " AND status = :status";
        }
        
        $stmt = $conn->prepare($q);
        $stmt->execute($params);

        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Retrieves a list of appointments received by a professor
     * 
     * @param array $params {
     *      @type string professor_user_id
     *      @type string status
     * }
     */
    public static function getAllRecievedAppointments(array $params):array
    {
        $conn = Database::get()->connect();

        $q = <<<SQL
            SELECT
                apt.id,
                apt.status,
                apt.message,
                apt.target_date,
                av.day_of_week,
                av.start_time,
                av.end_time,
                u.name
            FROM appointments apt
            LEFT JOIN availability av ON apt.availability_id = av.id
            LEFT JOIN users u ON apt.student_user_id = u.id
            WHERE av.user_id = :professor_user_id
            ORDER BY apt.target_date ASC
        SQL;
        
        if (isset($params['status'])) {            
            $q .= " AND status = :status";
        }
        
        $stmt = $conn->prepare($q);
        $stmt->execute($params);

        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Updates the status of an appointment
     * 
     * @param array $params {
     *      @type string professor_user_id  - id of the logged in professor, who recieved the appointment
     *      @type string id                 - id of the appointment to be updated
     *      @type string status             - new status of the appointment
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
                AND apt.status = 'pending'
        SQL);

        $stment->execute($params);

        $rowCount = $stment->rowCount();

        return $rowCount;
    }
    /**
     * Deletes a specific appointment, based on a given
     * id
     * 
     * @param array $params {
     *      @type string student_user_id
     *      @type string id
     * }
     */
    public static function delete(array $params):int
    {
        $conn = Database::get()->connect();

        $stment = $conn->prepare(<<<SQL
            DELETE FROM appointments
            WHERE
                student_user_id = :student_user_id
                AND id = :id
        SQL);

        $stment->execute($params);

        $rowCount = $stment->rowCount();

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
}
