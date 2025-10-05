<?php
require_once __DIR__ . '/config/headers.php';

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
                $professor->getAvailability(true);
                break;
            case "DELETE":
                $professor->removeAvailability();
        }
    break;

    case "/search/professor":
        $professor->search();
        break;

    case "/search/professor/info":
        $professor->getInfo();
        break;

    case "/search/availability":
        $professor->getAvailability(false);
        break;

    case "/appointment/send":
        $appointment->send();
        break;

    case "/appointment/list":
        $appointment->getList();   
        break;

    case "/appointment/accept":
        $appointment->accept();
        break;

    case "/appointment/update/message":
        $appointment->updateMessage();
        break;

    case "/appointment/delete":
        if ($method === 'DELETE')
            $appointment->delete();
        break;

    case "/appointment/currentDayBooked":
        $appointment->getCurrentDayBooked();
        break;

    case "/user/me":
        if($method === 'GET')
            $auth->me();
        break;

    case "/appointment/count":
        if($method === 'POST')
            $appointment->getCurrentAppointmentsCount();
        break;

    case "/appointment/groupedCount":
        if($method === 'POST')
            $appointment->getGroupedAppointmentsCount();
       break;

    default:
        http_response_code(404);
        echo Response::create(false, "Request Does Not Exist", null);
        exit;
}