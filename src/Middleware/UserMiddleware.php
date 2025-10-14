<?php

namespace App\Middleware;

use App\Http\Cookie;
use App\Http\Response;

class UserMiddleware {

    /**
     * Ensures that user request role are match to restrict
     * a certain permission
     */
    public static function requireRole(
        string $requiredRole
    ): void
    {
        $user = Cookie::getUser();
        $role = $user->role;

        if ($role != $requiredRole){
            Response::sendJson(403, false, "Invalid Role", null);
        }
    }
}
