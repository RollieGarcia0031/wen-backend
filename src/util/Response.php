<?php

namespace App\Http;

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
}