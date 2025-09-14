<?php

require_once __DIR__ . '/../model/Professor.php';
require_once __DIR__ . '/../util/Response.php';

class ProfessorController {
    private $professor;

    public function __construct() {
        $this->professor = new Professor();
    }

    public function addProfile($year, $department) {
        $uid = $_SESSION['uid'];
        if(!$uid) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->professor->addProfile($year, $department, $uid);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;
            
            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function addAvailability($day, $start, $end){
        $user_id = $_SESSION['uid'];

        if(!$user_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->professor->addAvailability($user_id, $day, $start, $end);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            return Response::create($sucess, $message, $data);

        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    /**
     * Returns the availability of professor
     * if no id is provided, it will return the availability
     * of the logged in user which is dapat ay professor
     * @param int|null $uid
     */
    public function getAvailability($uid = null){
        $user_id = $uid ?? $_SESSION['uid'];

        if(!$user_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->professor->getAvailability($user_id);

            http_response_code($this->professor->code);
            return Response::create($sucess, $this->professor->message, $this->professor->data);

        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    function removeAvailability($id) {
        $user_id = $_SESSION['uid'];

        if(!$user_id) {
            http_response_code(401);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->professor->removeAvailability($id);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;
            
            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function search($name, $day, $time_start, $time_end, $department, $year) {
        try{
            $sucess = $this->professor->search($name, $day, $time_start, $time_end, $department, $year);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;
            
            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function getProfile(){
        $user_id = $_SESSION['uid'];

        if(!$user_id) {
            http_response_code(201);
            return Response::create(false, "User not logged in", null);
        }

        try{
            $sucess = $this->professor->getProfile($user_id);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function removeProfile($id){
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            return Response::create(false, "User not logged in", null);
        }

        if (!isset($id)) {
            http_response_code(400);
            return Response::create(false, "Id not provided", null);
        }

        try{
            $sucess = $this->professor->removeProfile($id, $_SESSION['uid']);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    /**
     * Returns the info of a professor
     * this will be used by students to view information about a certain professor
     * @param int $prof_id professor id to be searched
     */
    public function getInfo($prof_id){
        try{
            $sucess = $this->professor->getInfo($prof_id);
            $message = $this->professor->message;
            $data = $this->professor->data;
            $code = $this->professor->code;

            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }
}