<?php

use App\Controller\AuthController;

$routes = [];

$routes["POST"] = [
    "/auth/register" => [ AuthController::class, "register" ],
    "/auth/login" => [ AuthController::class, "login" ] 
];
