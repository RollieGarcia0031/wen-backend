<?php

namespace App;

use App\Base\Controller;

use App\Model\User;
use App\Http\Request;
use App\Http\Response;
use PDOException;

class AuthController extends Controller{    
    public function register(){
        $body = Request::getBody();
        
        $name = $body['name'];
        $email = $body['email'];
        $password = $body['password'];
        $role = $body['role'];

        try {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);

            Response::sendJson(
                200,
                true,
                'User created successfully'
            );

        } catch (PDOException $e) {
            Response::sendJson(
                500,
                false,
                $e->getMessage(),
                null
            );
        }
    }

    public function login(){
        
    }
}