<?php

require_once __DIR__ . '/AppModel.php';
require_once __DIR__ . '/Professor.php';

class Appointment extends AppModel{
    private $professor;

    /**
     * sends an appointment to a professor
     * @param int $prof_id id of the professor (reciever)
     * @param int $student_id id of the student (sender)
     * @param int $availability_id id of the availability (to get the day and time)
     * @param string $message_text text of the message
     * @param string $time_stamp timestamp
     */
    public function send($prof_id, $student_id, $availability_id, $message_text, $time_stamp){
        $this->professor = new Professor();
        $is_professor = $this->professor->isVerified($prof_id);

        if (!$is_professor) {
            $this->code = 401;
            $this->message = "Sent id is not a professor";
            return false;
        }

        $query = "INSERT INTO appointments (professor_id, student_id, availability_id, message, time_stamp)
            VALUES (?, ?, ?, ?, ?)";

        $stment = $this->db->prepare($query);
        
        $execute = $stment->execute([$prof_id, $student_id, $availability_id, $message_text, $time_stamp]);

        if (!$execute) {
            $this->code = 500;
            $this->message = "Error sending appointment";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Appointment sent successfully";
        $this->data = ['id' => $this->db->lastInsertId()];
        return true;
    }

    /**
     * Fetches a list of Appointments
     * if logged user is a student, it shows sent appointments
     * if logged user is a professor, it shows received appointments
     * 
     * @param int $user_id id of logged user
     */
    public function getList($user_id){
        //get user's role
        $query = "SELECT role FROM users WHERE id = ?";
        
        $stment = $this->db->prepare($query);
        $stment->execute([$user_id]);

        $userRole = $stment->fetch(PDO::FETCH_ASSOC)['role'];

        // get appointments, merges with users and professors
        $query2 = "SELECT
            a.id as appointment_id,
            a.student_id,
            a.professor_id,
            a.status,
            a.message,
            a.time_stamp,
            u.name,
            av.day_of_week,
            av.start_time,
            av.end_time
        FROM appointments a
        LEFT JOIN availability av ON a.availability_id = av.id
        ";

        //adds conditions depending on user's role
        if ($userRole === 'professor'){
            $query2 .= "LEFT JOIN users u ON u.id = a.student_id
            WHERE a.professor_id = ?";
        } else if ($userRole === 'student'){
            $query2 .= "LEFT JOIN users u ON u.id = a.professor_id
            WHERE a.student_id = ?";
        } else {
            $this->message = "Role not Found";
            $this->code = 401;
            return false;
        }

        // order from old to new
        $query2 .= " ORDER BY a.time_stamp ASC";

        $stment = $this->db->prepare($query2);
        $execute = $stment->execute([$user_id]);
        $appointements = $stment->fetchAll(PDO::FETCH_ASSOC);

        if (!$execute){
            $this->message = "Execution Failed";
            $this->code = 400;
            return false;
        }

        if(!$appointements){
            $this->message = "No appointments found";
            $this->code = 200;
            return true;
        }

        $viewer = null;
        $names = [];
        if ($userRole === 'professor'){
            $viewer = 'student_id';
        } else if ($userRole === 'student'){
            $viewer = 'professor_id';
        }

        // fetch the names, instead of using join statements
        $names = [];
        $viewer_ids = array_values( array_unique(  array_column($appointements, $viewer) ) );
        
        $placeholder = str_repeat('?, ', count($viewer_ids) - 1) . '?';
        $query3 = "SELECT id, name FROM users WHERE id IN ($placeholder)";
        $stment = $this->db->prepare($query3);
        $stment->execute($viewer_ids);
        $names = $stment->fetchAll(PDO::FETCH_ASSOC);

        $this->data = [
            "role" =>  $userRole,
            "appointments" => $appointements,
            "names" => $names
        ];
        $this->message = "Query Sucess";
        $this->code = 200;
        return true;
    }

    /**
     * Accepts an appointement, only works if logged user is a professor
     * @param int $appointement_id
     * @param int $user_id id of logged professor
     */
    public function accept($appointement_id, $user_id){
        $query1 = "UPDATE appointments SET status = 'confirmed'
            WHERE id = ? AND professor_id = ?";

        $stment = $this->db->prepare($query1);
        $execute = $stment->execute([$appointement_id, $user_id]);

        if (!$execute) {
            $this->code = 500;
            $this->message = "Error accepting appointment";
            return false;
        }

        $result = $stment->rowCount();
        if (!$result) {
            $this->code = 404;
            $this->message = "Appointment not found";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Appointment accepted successfully";
        return true;
    }

    public function delete($appointement_id){
        $user_id = $_SESSION['uid'];

        $query1 = "DELETE FROM appointments
            WHERE id = ? AND (professor_id = ? OR student_id = ?)";

        $stment = $this->db->prepare($query1);
        $execute = $stment->execute([$appointement_id, $user_id, $user_id]);

        if (!$execute) {
            $this->code = 500;
            $this->message = "Error deleting appointment";
            return false;
        }

        $result = $stment->rowCount();
        if (!$result) {
            $this->code = 404;
            $this->message = "Appointment not found";
            return false;
        }
        
        $this->code = 200;
        $this->message = "Appointment deleted successfully";
        return true;
    }

    /**
     * Updates the message of an appointement
     * @param int $appointement_id
     * @param string $new_message
     * @param int $student_id used to confirm it student owns the message to be edited
     */
    public function updateMessage($appointement_id, $new_message, $student_id){
        $q = "UPDATE appointments SET message = ? WHERE id = ? AND student_id = ?";

        $statement = $this->db->prepare($q);
        $execute = $statement->execute([$new_message, $appointement_id, $student_id]);

        if(!$execute) {
            $this->code = 500;
            $this->message = "Error updating message";
            return false;
        }

        $rowCount = $statement->rowCount();
        if (!$rowCount) {
            $this->code = 404;
            $this->message = "Appointment not found";
            $this->data = ['id' => $appointement_id];
            return false;
        }
        
        $this->code = 200;
        $this->message = "Message updated successfully";
        return true;
    }

    /**
     * Returns all the appointments for the current day for the logged user
     * @param int $user_id
     */
    function getCurrentDayBooked($user_id){
        $current_time = date('Y-m-d');

        $q1 = "SELECT role FROM users WHERE id = ?";
        $stment = $this->db->prepare($q1);
        $stment->execute([$user_id]);

        $userRole = $stment->fetch(PDO::FETCH_ASSOC);
        if($userRole === null){
            $this->code = 404;
            $this->message = "User not found";
            return false;
        }

        if($userRole['role'] == 'professor'){
            $query = "SELECT
                a.*,
                u.name,
                av.start_time
            FROM appointments a
            INNER JOIN users u
                ON (a.student_id = u.id)
            INNER JOIN availability av
                ON a.availability_id = av.id
            WHERE
                a.professor_id = ?
                AND a.status = 'confirmed'
                AND DATE(a.time_stamp) = ?
            ORDER BY av.start_time ASC
            ";   
        } else {
            $query = "SELECT
                    a.*,
                    u.name,
                    av.start_time
                FROM appointments a
                INNER JOIN users u
                    ON (a.professor_id = u.id)
                INNER JOIN availability av
                    ON a.availability_id = av.id
                WHERE
                    a.student_id = ?
                    AND a.status = 'confirmed'
                    AND DATE(a.time_stamp) = ?
                ORDER BY av.start_time ASC
                ";
        }

        $statement = $this->db->prepare($query);
        $execute = $statement->execute([$user_id, $current_time]);

        if(!$execute) {
            $this->code = 500;
            $this->message = "Error getting appointments";
            return false;
        }

        $appointements = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->data = $appointements;
        $this->code = 200;
        $this->message = "Success";
        return true;
    }
}