<?php

namespace App\Base;

use App\Database\Database;
use PDO;

class Controller {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::get()->connect();
    }
}