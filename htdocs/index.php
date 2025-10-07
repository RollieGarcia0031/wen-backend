<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Web/Route.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$frontend_domain = $_ENV['FRONTEND_DOMAIN'];

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . $frontend_domain);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(200);
}

session_start();

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if (isset($routes[$method][$url])){
    [ $class, $method ] = $routes[$method][$url];
    $instance = new $class();
    $instance->$method();
} else {
    App\Http\Response::sendJson(
        404,
        false,
        "Not found"
    );
}
