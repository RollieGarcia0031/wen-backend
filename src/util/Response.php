<?php

class Response {
    public static function create(bool $success, string $message, $data = null, int $code = 400) {
        $code = $success ? 200 : $code;
        http_response_code($code);
        return json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], true);
    }
}