<?php

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;

class RequestMiddleware {
    /**
     * Checks if the requested json contains all
     * of the required keys
     *
     * @param $keys the array of keys that shall be found
     * in the request
     */
    public static function requireFields(array $keys):void
    {
        $data = Request::getBody();

        foreach ($keys as $key){
            $input = $data[$key];
            if (!isset($input)){
                Response::sendJson(
                    402, false,
                    "Invalid/Incomplete Request",
                    null
                );
            }
        }  
    }
}
