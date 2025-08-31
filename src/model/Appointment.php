<?php

require_once __DIR__ . '/AppModel.php';
require_once __DIR__ . '/Professor.php';

class Appointment extends AppModel{
    private $professor;

    public function send($prof_id, $student_id, $time_stamp){
        $this->professor = new Professor();
        $is_professor = $this->professor->isVerified($prof_id);

        if (!$is_professor) {
            $this->code = 401;
            $this->message = "Sent id is not a professor";
            return false;
        }

        $query = "INSERT INTO appointments (professor_id, student_id, appointment_time)
            VALUES (?, ?, ?)";

        $stment = $this->db->prepare($query);
        
        $execute = $stment->execute([$prof_id, $student_id, $time_stamp]);

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

    public function getList($user_id){
        $query = "SELECT role FROM users WHERE id = ?";
        
        $stment = $this->db->prepare($query);
        $stment->execute([$user_id]);

        $userRole = $stment->fetch(PDO::FETCH_ASSOC)['role'];

        $query2 = "SELECT
            p.department as prof_department,
            p.year as prof_year,
            a.id as apt_id,
            a.professor_id as professor_id,
            a.student_id as student_id,
            a.appointment_time as appointment_time,
            a.status as status

            FROM appointments a
            JOIN professors p ON p.user_id = a.professor_id
            WHERE 1 = 1";

        if ($userRole === 'professor'){
            $query2 .= " AND a.professor_id = ?";
        } else if ($userRole === 'student'){
            $query2 .= " AND a.student_id = ?";
        } else {
            $this->message = "Role not Found";
            $this->code = 401;
            return false;
        }

        $stment = $this->db->prepare($query2);
        $execute = $stment->execute([$user_id]);
        $appointements = $stment->fetchAll(PDO::FETCH_ASSOC);

        if (!$execute){
            $this->message = "Execution Failed";
            $this->code = 400;
            return false;
        }

        if (!$appointements){
            $this->message = "No Appointments Found";
            $this->code = 404;
            return false;
        }

        $viewer = null;
        $names = [];
        if ($userRole === 'professor'){
            $viewer = 'student_id';
        } else if ($userRole === 'student'){
            $viewer = 'professor_id';
        }

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
}