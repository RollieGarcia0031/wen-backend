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
        [
            'email' => $email,
            'password' => $password
        ] = Request::getBody();

        try {

            $user = User::getByEmail($email);

            $verified_password = password_verify($password, $user->password);

            if($user && $verified_password){
                Response::sendJson(
                    200,
                    true,
                    'User logged in successfully',
                    [
                        "id"=> $user->id,
                        "name" => $user->name,
                        "email" => $user->email
                    ]
                );

                $_SESSION["user"] = $user;
            }

            Response::sendJson(
                401,
                false,
                'Invalid credentials',
                null
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
}