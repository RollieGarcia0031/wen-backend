<?php

require_once __DIR__ . '/../model/Appointment.php';
require_once __DIR__ . '/../util/Response.php';

class AppointmentController {
    private $appointment;

    public function __construct()
    {
        $this->appointment = new Appointment();
    }

    public function send($prof_id, $time_stamp){
        $student_id = $_SESSION['uid'];

        if (!$student_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try {
            $sucess = $this->appointment->send($prof_id, $student_id, $time_stamp);
            $message = $this->appointment->message;
            $data = $this->appointment->data;
            $code = $this->appointment->code;

            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function getList(){
        $user_id = $_SESSION['uid'];

        if (!$user_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try {
            $sucess = $this->appointment->getList($user_id);
            $message = $this->appointment->message;
            $data = $this->appointment->data;
            $code = $this->appointment->code;
    
            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function accept($appointment_id){
        $user_id = $_SESSION['uid'];

        if (!$user_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try {
            $sucess = $this->appointment->accept($appointment_id, $user_id);
            $message = $this->appointment->message;
            $data = $this->appointment->data;
            $code = $this->appointment->code;
    
            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }
}