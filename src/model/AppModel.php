<?php

require_once __DIR__ . '/../config/db.php';

class AppModel {
    protected $db;
    public string $message;
    public int $code = 200;
    public array $data = [];

    public function __construct()
    {
        $this->db = connection();
    }
}