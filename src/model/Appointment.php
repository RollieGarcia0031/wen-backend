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
}