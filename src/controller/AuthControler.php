<?php

require_once __DIR__ . '/../util/Response.php';
require_once __DIR__ . '/../model/Auth.php';
require_once __DIR__ . '/../util/getRequestJson.php';

class AuthController {
    private $auth;

    public function __construct(){
        $this->auth = new Auth();
    }

    public function login(){
        $data = getRequestJson();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        try {            
            $login = $this->auth->login($email, $password);
            $data = $this->auth->data;
            $message = $this->auth->message;
                
            http_response_code($this->auth->code);

            echo Response::create(
                $login,
                $message,
                $data
            );

        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, "Login failed", $e->getMessage());
        }    
    }

    public function signup($name, $email, $password, $role) {
        try {
            $signup = $this->auth->signup($email, $name, $password, $role);
            if ($signup) {
                $data = $this->auth->data;
                $message = $this->auth->message;

                http_response_code($this->auth->code);
                return Response::create($signup, $message, $data);
            }
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, "Signup failed", $e->getMessage());
        }
    }

    public function logout(){
        $success = $this->auth->logout();
        
        http_response_code(200);
        return Response::create($success, "Logout successful", null);
    }

    public function me(){
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            return Response::create(false, "User not logged in", null);
        }

        try {
            $sucess = $this->auth->me($_SESSION['uid']);
            $message = $this->auth->message;
            $data = $this->auth->data;
            $code = $this->auth->code;

            http_response_code($code);
            return Response::create($sucess, $message, $data);
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }

    public function update($email, $name, $old_password, $new_password){
        if (!isset($_SESSION['uid'])) {
            http_response_code(401);
            return Response::create(false, "User not logged in", null);
        }

        try {
            $success = $this->auth->updateInfos(
                $email, $name, $old_password, $new_password, $_SESSION['uid']
            );

            $message = $this->auth->message;
            $data = $this->auth->data;
            $code = $this->auth->code;

            http_response_code($code);
            return Response::create($success, $message, $data);
        } catch (PDOException $e){
            http_response_code(500);
            return Response::create(false, $e->getMessage(), null);
        }
    }
}