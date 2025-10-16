<?php
namespace App\Http;

use Exception;

class Request {
    /**
     * Returns the body of the request as
     * an assocative array
     */
    public static function getBody():array{
        $input = file_get_contents('php://input');

        if (trim($input) === '') return [];

        $data =  json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) return [];

        return is_array($data) ? $data : [];
    }
}
