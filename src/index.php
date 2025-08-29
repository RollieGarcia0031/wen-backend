<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/controller/AuthControler.php';

session_start();

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new AuthController();

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri){
    case "/auth/login":
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'];
        $password = $data['password'];

        echo $auth->login($email, $password);

        break;
    case "/auth/signup":
        $data = json_decode(file_get_contents('php://input'), true);

        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password'];
        $role = $data['role'];

        echo $auth->signup($name, $email, $password, $role);
        break;

    case "/auth/logout":
        echo $auth->logout(); 
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found', 'data' => null], true);
        exit;
}