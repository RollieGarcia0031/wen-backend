<?php

class Response {
    public static function create(bool $success, string $message, $data = null) {
        return json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
}