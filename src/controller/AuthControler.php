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
            exit;

        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, "Login failed", $e->getMessage());
            exit;
        }    
    }

    public function signup() {
        $data = getRequestJson();

        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $role = $data['role'] ?? null;

        try {
            $signup = $this->auth->signup($email, $name, $password, $role);
            if ($signup) {
                $data = $this->auth->data;
                $message = $this->auth->message;

                http_response_code($this->auth->code);
                echo Response::create(
                    $signup,
                    $message,
                    $data
                );
                exit;
            }
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, "Signup failed", $e->getMessage());
            exit;
        }
    }

    public function logout(){
        $success = $this->auth->logout();
        
        http_response_code(200);
        echo Response::create($success, "Logout successful", null);
        exit;
    }

    public function me(){
        if(!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }

        try {
            $sucess = $this->auth->me($_SESSION['uid']);
            $message = $this->auth->message;
            $data = $this->auth->data;
            $code = $this->auth->code;

            http_response_code($code);
            echo Response::create($sucess, $message, $data);
            exit;
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
            exit;
        }
    }

    public function update(){
        if (!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(false, "User not logged in", null);
            exit;
        }
        

        $data = getRequestJson();
        
        $email = $data['email'] ?? null;
        $name = $data['name'] ?? null;
        $old_password = $data['old_password'] ?? null;
        $new_password = $data['new_password'] ?? null;

        try {
            $success = $this->auth->updateInfos(
                $email, $name, $old_password, $new_password, $_SESSION['uid']
            );

            $message = $this->auth->message;
            $data = $this->auth->data;
            $code = $this->auth->code;

            http_response_code($code);
            echo Response::create($success, $message, $data);
        } catch (PDOException $e){
            http_response_code(500);
            echo Response::create(false, $e->getMessage(), null);
        }
    }
}
