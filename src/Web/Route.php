<?php

namespace App\Web;
use App\Controller\AuthController;
use App\Http\Response;

class Route {
    private static array $routes = [];

    public static function initRoutes()
    {
        self::$routes["POST"] = [
            "/auth/login" => [AuthController::class, "login"],
            "/auth/register" => [AuthController::class, "register"]
        ];
    }

    public static function handleRoute($url, $method){
        // Ensure routes are initialized before use
        if (empty(self::$routes)) {
            self::initRoutes();
        }

        if (!isset(self::$routes[$method][$url])) {
            Response::sendJson(
                404,
                false,
                "File not found",
                null,
            );
        }
    
        [ $controller, $method ] = self::$routes[$method][$url];
    
        $controller = new $controller();
        $controller->$method();
    }
}