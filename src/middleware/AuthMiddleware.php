<?php

require_once __DIR__ . '/../util/Response.php';

class AuthMiddleware {

    /**
     * Checks if the current session has a registered user id
     * 
     * If sender of the request has no uid set in the current session,
     * it will response with a 401 error
     * 
     * This middleware shall be used for routes that requires users to login
     * prior to the requested route
     */
    public static function requireAuth(){
        if (!isset($_SESSION['uid'])) {
            http_response_code(401);
            echo Response::create(
                false,
                "User not logged in",
                null
            );

            exit;
        }
    }
}
