<?php

namespace App\Controller;

use App\Base\Controller;
use App\Http\Cookie;
use App\Model\User;
use App\Http\Request;
use App\Http\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\RequestMiddleware;
use Exception;
use PDOException;

class AuthController extends Controller{
    /**
     * Creates a new user account
     */
    public function register(){
        RequestMiddleware::requireFields(['name', 'email', 'password', 'role']);

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

            $message = "User created successfully";

            Response::sendJson(200, true, $message, null);

        } catch (PDOException $error) {
            Response::sendError($error);
        }
    }

    /**
     * Allows both student and professor to login their account
     */
    public function login(){
        RequestMiddleware::requireFields(['email', 'password']);

        // extract the email and password from the
        // JSON input of API request
        [
            'email' => $email,
            'password' => $password
        ] = Request::getBody();

        try {
            // retrieve the user
            $user = User::getByEmail($email);

            $verified_password = password_verify($password, $user->password);

            if($user && $verified_password){
                $user->password = "*";

                $_SESSION["user"] = $user;

                $message = "User logged in successfully";
                $data = [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email
                ];

                Response::sendJson(200, true, $message, $data);
            }

            $message = "Invalid credentials";
            Response::sendJson(401, false, $message, null);

        } catch (PDOException $error) {
            Response::sendError($error);
        } catch (Exception $error) {
            Response::sendError($error);
        }
        
    }

    /**
     * Get information about the logged user
     */
    public static function getProfile(){
        AuthMiddleware::requireAuth();

        try {
            $user = Cookie::getUser();

            if (!isset($user)){
                Response::sendJson(401, false, "Not Logged In", null);
            }

            Response::sendJson(200, true, "User Logged", (array)$user);
        } catch (Exception $error) {
            Response::sendError($error);
        }
    }

    /**
     * logs the user out of the system
     */
    public function logout(){
        session_destroy();
        Response::sendJson(200, true, "User logged out", null);
    }
}
