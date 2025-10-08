<?php

namespace App\Http;

use Exception;

class Response {
    public static function sendJson(
        int $code = 200,
        bool $sucess,
        string $message,
        ?array $data = null
    ): void {
        http_response_code($code);
        echo json_encode([
            'success' => $sucess,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Sends a generic error response with a level 500 error
     * to the clien
     */
    public static function sendError(Exception $e){
        Response::sendJson(
            500,
            false,
            $e->getMessage(),
            null
        );
    }
}
