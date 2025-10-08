<?php

namespace App\Middleware;

use App\Http\Cookie;
use App\Http\Response;

class AuthMiddleware {
    public static function requireAuth(){
        $user = Cookie::getUser();

        if (isset($user)) return;

        Response::sendJson(
            401,
            false,
            "User not logged in",
            null
        );
    }
}
