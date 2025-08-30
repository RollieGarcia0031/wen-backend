<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/controller/AuthControler.php';
require_once __DIR__ . '/controller/ProfessorController.php';

session_start();

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new AuthController();
$professor = new ProfessorController();

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri){
    case "/auth/login":
        $data = json_decode(file_get_contents('php://input'), true);
        
        ['email'=>$email, 'password'=>$password] = $data;

        echo $auth->login($email, $password);

    break;
    case "/auth/signup":
        $data = json_decode(file_get_contents('php://input'), true);

        [
            'name'=>$name,
            'email'=>$email,
            'password'=>$password,
            'role'=>$role
        ] = $data;

        echo $auth->signup($name, $email, $password, $role);
    break;

    case "/auth/logout":
        echo $auth->logout(); 
    break;

    case "/professor/profile":
        switch ($method) {
            case "POST":
                $data = json_decode( file_get_contents('php://input'), true );
                ['year'=>$year, 'department'=>$department] = $data;
                echo $professor->addProfile($year, $department);
            break;
        }
    break;

    case "/professor/availability":
        switch ($method) {
            case "POST":
                $data = json_decode( file_get_contents('php://input'), true );
                ['day'=>$day, 'start'=>$start, 'end'=>$end] = $data;
                echo $professor->addAvailability($day, $start, $end);
            break;
            
            case "GET":
                echo $professor->getAvailability(null);
        }
    break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found', 'data' => null], true);
        exit;
}