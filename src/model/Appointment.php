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

        $query2 = null;

        if ($userRole === 'professor'){
            $query2 = "SELECT * FROM appointments WHERE professor_id = ?";           
        } else if ($userRole === 'student'){
            $query2 = "SELECT * FROM appointments WHERE student_id = ?";
        } else {
            $this->message = "Role not Found";
            $this->code = 401;
            return false;
        }
        $stment = $this->db->prepare($query2);
        $execute = $stment->execute([$user_id]);
        $result = $stment->fetchAll(PDO::FETCH_ASSOC);

        if (!$execute){
            $this->message = "Execution Failed";
            $this->code = 400;
            return false;
        }

        $this->data = [
            "role" =>  $userRole,
            "appointments" => $result
        ];
        $this->message = "Query Sucess";
        $this->code = 200;
        return true;
    }
}