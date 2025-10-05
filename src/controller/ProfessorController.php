<?php

require_once __DIR__ . '/../model/Professor.php';
require_once __DIR__ . '/../util/Response.php';
require_once __DIR__ . '/../util/getRequestJson.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ProfessorController {
    private $professor;

    public function __construct() {
        $this->professor = new Professor();
    }

    /**
     * Adds a profile to the logged in professor
     * the profile contains the year and department of the professor
     * this will be used by students to filter professors
     */
    public function addProfile() {
        AuthMiddleware::requireAuth();

        $uid = $_SESSION['uid'];

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

    /**
     * Allos professor to create an availability time slot for themselves
     * that will be used by students as choices for time of appointmnet
     */
    public function addAvailability(){
        AuthMiddleware::requireAuth();
    
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
        AuthMiddleware::requireAuth();
       
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
        AuthMiddleware::requireAuth();

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

    /**
     * Allows users students to search for a list of professor based on a given filters
     */
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

    /**
     * Allows users with student roles to view the departments and years that 
     * a professor is teaching
     */
    public function getProfile(){
        AuthMiddleware::requireAuth();

        $user_id = $_SESSION['uid'];

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

    /**
     * Allows users logged as professor to remove the profile of class that they are
     * teaching
     */
    public function removeProfile(){
        AuthMiddleware::requireAuth();

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
