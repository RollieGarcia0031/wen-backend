<?php

require_once __DIR__ . '/../util/Response.php';
require_once __DIR__ . '/../model/Auth.php';

class AuthController {
    private $auth;

    public function __construct(){
        $this->auth = new Auth();
    }

    public function login($email, $password){
        $login = $this->auth->login($email, $password);

        try {
            
            if ($login) {
                $data = $this->auth->data;
                $message = $this->auth->message;
                
                http_response_code(200);
                return Response::create(true, $message, $data);
            }

            http_response_code(401);
            return Response::create(false, $this->auth->message, null);

        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, "Login failed", $e->getMessage());
        }    
    }

    public function signup($name, $email, $password, $role) {
        try {
            $signup = $this->auth->signup($email, $name, $password, $role);
            if ($signup) {
                $data = $this->auth->data;
                $message = $this->auth->message;

                http_response_code(200);
                return Response::create(true, $message, $data);
            }
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, "Signup failed", $e->getMessage());
        }
    }

    public function logout(){
        $this->auth->logout();
        
        http_response_code(200);
        return Response::create(true, "Logout successful", null);
    }
}