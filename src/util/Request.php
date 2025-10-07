<?php
namespace App\Http;

class Request {
    /**
     * Returns the body of the request as
     * an assocative array
     */
    public static function getBody():array{
        return json_decode(file_get_contents('php://input'), true);
    }
}
