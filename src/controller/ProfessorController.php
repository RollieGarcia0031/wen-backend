<?php

require_once __DIR__ . '/../model/Professor.php';
require_once __DIR__ . '/../util/Response.php';
require_once __DIR__ . '/../util/getRequestJson.php';

class ProfessorController {
    private $professor;

    public function __construct() {
        $this->professor = new Professor();
    }

    public function addProfile() {
        $uid = $_SESSION['uid'];
        if(!$uid) {
            http_response_code(201);
            echo Response::create(false, "User not logged in", null);
            exit; 
        }

        $data =getRequestJson();

        $year = $data['year'] ?? null;
        $department = $data['department'] ?? null;

        try{
            $sucess = $this->professor->addProfile($year, $department, $uid);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;
            
            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    public function addAvailability(){
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }
        
        $user_id = $_SESSION['uid'];

        $data = getRequestJson();
        $day = $data['day'] ?? null;
        $start = $data['start'] ?? null;
        $end = $data['end'] ?? null;

        try{
            $sucess = $this->professor->addAvailability($user_id, $day, $start, $end);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    /**
     * Returns the availability of professor
     * if no id is provided, it will return the availability
     * of the logged in user which is dapat ay professor
     * @param bool $self
     */
    public function getAvailability($self = true){
        
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }

        $user_id = $self ? $_SESSION['uid'] : getRequestJson()['id'];
        
        try{
            $sucess = $this->professor->getAvailability($user_id);

            http_response_code($this->professor->code);
            echo Response::create($sucess, $this->professor->message, $this->professor->data);
            exit;
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }
    /**
     * Deletes an availability assigned to a professor
     */
    function removeAvailability() {
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }

        $appointmentId = getRequestJson()['id'];

        try{
            $sucess = $this->professor->removeAvailability($appointmentId);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;
            
            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    public function search() {
        $data = getRequestJson();

        $name = $data['name'] ?? null;
        $day = $data['day'] ?? null;
        $time_start = $data['time_start'] ?? null;
        $time_end = $data['time_end'] ?? null;
        $department = $data['department'] ?? null;
        $year = $data['year'] ?? null;

        try{
            $sucess = $this->professor->search($name, $day, $time_start, $time_end, $department, $year);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;
            
            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    public function getProfile(){
        $user_id = $_SESSION['uid'];

        if(!$user_id) {
            http_response_code(201);
            echo Response::create(false, "User not logged in", null);
            exit;
        }

        try{
            $sucess = $this->professor->getProfile($user_id);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    public function removeProfile(){
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            return Response::create(false, "User not logged in", null);
        }

        $data = getRequestJson();
        $id = $data['id'] ?? null;

        try{
            $sucess = $this->professor->removeProfile($id, $_SESSION['uid']);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    /**
     * Returns the info of a professor
     * this will be used by students to view information about a certain professor
     */
    public function getInfo(){
        $data = getRequestJson();
        // the id of the target professor to be searched
        $prof_id = $data['id'];

        try{
            $sucess = $this->professor->getInfo($prof_id);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }
}
