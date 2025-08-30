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
            if ($sucess) {
                return Response::create($sucess, $message, $data);
            } else {
                return Response::create($sucess, $message, $data);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }
}