<?php

namespace App\Web;
use App\Controller\AuthController;
use App\Http\Response;

$routes;

$routes["POST"] = [
    "/auth/login" => [AuthController::class, "login"],
    "/auth/register" => [AuthController::class, "register"]
];

class Route {
    public static function handleRoute($url, $method){
        global $routes;
        var_dump($routes);
        exit;
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
}