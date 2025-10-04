<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$frontend_domain = $_ENV['FRONTEND_DOMAIN'];

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . $frontend_domain);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

session_start();
require_once __DIR__ . '/controller/AuthControler.php';
require_once __DIR__ . '/controller/ProfessorController.php';
require_once __DIR__ . '/controller/AppointmentController.php';


if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new AuthController();
$professor = new ProfessorController();
$appointment = new AppointmentController();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($uri){
    case "/auth/login":
        $auth->login();
        break;
    case "/auth/signup":
        $auth->signup();
        break;
    case "/auth/update":
        if($method === 'PUT'){
            $auth->update();
        }
        break;
    case "/auth/logout":
        $auth->logout(); 
        break;
    case "/professor/profile":
        switch ($method) {
            case "POST":
                $professor->addProfile();
                break;
            case "GET":
                $professor->getProfile();
                break;
            case "DELETE":
                $professor->removeProfile();
                break;
        }
    break;

    case "/professor/availability":
        switch ($method) {
            case "POST":
                $professor->addAvailability();
                break;
            case "GET":
                echo $professor->getAvailability(null);
            break;

            case "DELETE":
                $data = json_decode( file_get_contents('php://input'), true );
                $id = $data['id'] ?? null;
                echo $professor->removeAvailability($id);
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

    case "/search/professor/info":
        $data = json_decode( file_get_contents('php://input'), true );
        $id = $data['id'];
        echo $professor->getInfo($id);
    break;

    case "/search/availability":
        $data = json_decode( file_get_contents('php://input'), true );
        ['id'=>$id] = $data;
        echo $professor->getAvailability($id);
    break;

    case "/appointment/send":
        $data = json_decode( file_get_contents('php://input'), true );
        [
            'prof_id'=>$prof_id,
            'availability_id'=>$availability_id,
            'message'=>$message,
            'time_stamp'=>$time_stamp
        ] = $data;

        echo $appointment->send($prof_id, $availability_id, $message, $time_stamp);
    break;

    case "/appointment/list":
        echo $appointment->getList();   
    break;

    case "/appointment/accept":
        $data = json_decode( file_get_contents('php://input'), true );
        $appointment_id = $data['id'];
        echo $appointment->accept($appointment_id);
    break;

    case "/appointment/update/message":
        $data = json_decode( file_get_contents('php://input'), true );
        $appointment_id = $data['id'] ?? null;
        $message = $data['message'] ?? '';
        echo $appointment->updateMessage($appointment_id, $message);
    break;

    case "/appointment/delete":
        if ($method === 'DELETE') {
            $data = json_decode( file_get_contents('php://input'), true );
            $appointment_id = $data['id'];
            echo $appointment->delete($appointment_id);
        }
    break;

    case "/appointment/currentDayBooked":
        echo $appointment->getCurrentDayBooked();
    break;

    case "/user/me":
        if($method === 'GET'){
            echo $auth->me();
            exit;
        }
    break;

    case "/appointment/count":
        if($method === 'POST'){
            $data = json_decode( file_get_contents('php://input'), true );
            $status = $data["status"] ?? null;
            $time_stamp = $data["time_range"] ?? null;

            echo $appointment->getCurrentAppointmentsCount($status, $time_stamp);
            exit;
        }
    break;

    case "/appointment/groupedCount":
        if($method === 'POST'){
            $data = json_decode( file_get_contents('php://input'), true );
            $time_range = $data["time_range"] ?? null;

            echo $appointment->getGroupedAppointmentsCount($time_range);
        }
    break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Not found', 'data' => null], true);
        exit;
}
