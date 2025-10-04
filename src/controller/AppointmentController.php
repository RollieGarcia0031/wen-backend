<?php

require_once __DIR__ . '/../model/Appointment.php';
require_once __DIR__ . '/../util/Response.php';

class AppointmentController {
    private $appointment;

    public function __construct()
    {
        $this->appointment = new Appointment();
    }

    public function send(){
        
        if (!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }
        
        $student_id = $_SESSION['uid'];

        $data = getRequestJson();
        $prof_id = $data['prof_id'] ?? null;
        $availability_id = $data['availability_id'] ?? null;
        $message_text = $data['message'] ?? null;
        $time_stamp = $data['time_stamp'] ?? null;

        try {
            $sucess = $this->appointment->send(
                $prof_id,
                $student_id,
                $availability_id,
                $message_text,
                $time_stamp
            );
            $message = $this->appointment->message;
            $data = $this->appointment->data;
            $code = $this->appointment->code;

            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    /**
     * Returns a list of appointments
     * if logged session is a student, it responses with sent appointments
     * if logged session is a professor, it responses with received appointments
     */
    public function getList(){
        if (!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }

        $user_id = $_SESSION['uid'];

        try {
            $sucess = $this->appointment->getList($user_id);
            $message = $this->appointment->message;
            $data = $this->appointment->data;
            $code = $this->appointment->code;
    
            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    public function accept(){
        if (!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }

        $user_id = $_SESSION['uid'];
        $appointment_id = getRequestJson()['id'];

        try {
            $sucess = $this->appointment->accept($appointment_id, $user_id);
            $message = $this->appointment->message;
            $data = $this->appointment->data;
            $code = $this->appointment->code;
    
            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    /**
     * deletes an appointment
     * this only works for student who sent the appointment or
     * professor who received the appointment
     */
    public function delete($appointment_id) {
        $user_id = $_SESSION['uid'];

        if (!$user_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->appointment->delete($appointment_id);
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

    public function updateMessage($appointment_id, $message_text) {
        $student_id = $_SESSION['uid'];

        if (!$student_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->appointment->updateMessage($appointment_id, $message_text, $student_id);
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

    function getCurrentDayBooked(){
        if(!isset($_SESSION['uid'])) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }
        
        try {
            $sucess = $this->appointment->getCurrentDayBooked($_SESSION['uid']);
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

    public function getCurrentAppointmentsCount($status, $time_stamp){
        if (!isset( $_SESSION['uid'] )){
            http_response_code(401);
            return Response::create(false, "User not logged in");
        }

        $user_id = $_SESSION['uid'];

        try {
           $sucess = $this->appointment->getAppointmentsCount($user_id, $status, $time_stamp);
           
           http_response_code($this->appointment->code);

           return Response::create(
            $sucess,
            $this->appointment->message,
            $this->appointment->data
           );

        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function getGroupedAppointmentsCount($time_range){
        if (!isset( $_SESSION['uid'] )){
            http_response_code(401);
            return Response::create(false, "User not logged in");
        }

        $user_id = $_SESSION['uid'];

        try {
           $sucess = $this->appointment->getGroupedAppointmentsCount($user_id, $time_range);
           
           http_response_code($this->appointment->code);

           return Response::create(
            $sucess,
            $this->appointment->message,
            $this->appointment->data
           );

        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }
}