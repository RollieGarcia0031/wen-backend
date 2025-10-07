<?php

namespace App\Http;
use App\AuthController;
use App\Http\Response;

$routes = [];

$route["POST"] = [
    "/auth/login" => [AuthController::class, "login"],
    "/auth/register" => [AuthController::class, "register"]
];

function handleRoute($url, $method){
    global $routes;

    if (!isset($routes[$method][$url])) {
        Response::sendJson(
            404,
            false,
            "File not found",
            null,
        );
    }

    [ $controller, $method ] = $routes[$method][$url];

    $controller = new $controller();
    $controller->$method();
}