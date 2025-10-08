<?php

use App\Controller\AuthController;
use App\Controller\CourseController;
use App\Middleware\AuthMiddleware;

$routes = [];

$routes["POST"] = [
    "/auth/register"    => [ AuthController::class, "register" ],
    "/auth/login"       => [ AuthController::class, "login" ],

    "/course/create"    => [ CourseController::class, "create" ],
    "/course/use"       => [] 
];

$routes["GET"] = [
    "/auth/profile"     => [ AuthController::class, "getProfile" ]
];
