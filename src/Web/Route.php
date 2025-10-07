<?php

use App\Controller\AuthController;
use App\Controller\CourseController;

$routes = [];

$routes["POST"] = [
    "/auth/register" => [ AuthController::class, "register" ],
    "/auth/login" => [ AuthController::class, "login" ],

    "/course/create" => [ CourseController::class, "create" ] 
];
