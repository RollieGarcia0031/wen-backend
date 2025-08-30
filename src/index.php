<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/controller/AuthControler.php';
require_once __DIR__ . '/controller/ProfessorController.php';
require_once __DIR__ . '/controller/AppointmentController.php';

session_start();

if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new AuthController();
$professor = new ProfessorController();
$appointment = new AppointmentController();

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

    case "/search/professor":
        $data = json_decode( file_get_contents('php://input'), true );
        
        $name = $data['name'] ?? null;
        $day = $data['day'] ?? null;
        $time_start = $data['time_start'] ?? null;
        $time_end = $data['time_end'] ?? null;
        $department = $data['department'] ?? null;
        $year = $data['year'] ?? null;

        echo $professor->search($name, $day, $time_start, $time_end, $department, $year);
    break;

    case "/search/availability":
        $data = json_decode( file_get_contents('php://input'), true );
        ['id'=>$id] = $data;
        echo $professor->getAvailability($id);
    break;

    case "/appointment/send":
        $data = json_decode( file_get_contents('php://input'), true );
        ['prof_id'=>$prof_id, 'time_stamp'=>$time_stamp] = $data;

        echo $appointment->send($prof_id, $time_stamp);
    break;

    case "/appointment/list":
        echo $appointment->getList();   
    break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found', 'data' => null], true);
        exit;
}