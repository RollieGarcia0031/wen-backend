<?php

function connection(){
    $user = 'postgres';
    $password = '123456';
    $db = 'postgres';
    $host = 'localhost';
    $port = '5433';

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