<?php

require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

function connection(){
    $user = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];
    $db = $_ENV['DB_NAME'];
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];

    try{
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo 'Connection failed: ' . $e->getMessage();
        exit();
    }
}