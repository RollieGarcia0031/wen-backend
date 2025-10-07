<?php

namespace App\Database;
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use Exception;
use App\Http\Response;

class Database {
    private static ?Database $instance = null;
    private PDO $conn;

    /**
     * The main method to get the database instance
     */
    public static function get(){
        if (!isset(self::$instance)){
            self::$instance = new Database();
        }
        return self::$instance;
    }   

    /**
     * To obtain the database connection
     */
    public function connect(){
        return $this->conn;
    }

    /**
     * Database constructor, only called once to connect
     * to the database
     */
    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];
        $database = $_ENV['DB_NAME'];
        $port = $_ENV['DB_PORT'];

        $dsn = "pgsql:host=$host;port=$port;dbname=$database";

        try {
            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (Exception $e){
            Response::sendJson(
                500,
                false,
                $e->getMessage(),
                null
            );
        }
    }
}