<?php
// public/index.php
require_once __DIR__ . '/../app/database.php';
require_once __DIR__ . '/../app/models/grid.php';
require_once __DIR__ . '/../app/controllers/gridcontroller.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PATCH");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

function sendResponse($status, $code, $data, $message) {
    http_response_code($code);
    echo json_encode([
        "status" => $status,
        "code" => $code,
        "data" => $data,
        "message" => $message,
        "timestamp" => date("Y-m-d\TH:i:s.000\Z"),
        "service" => "grid-service"
    ]);
    exit;
}

use app\controllers\gridcontroller;
$gridController = new gridcontroller();

if ($uri === '/health' && $method === 'GET') {
    sendResponse("success", 200, null, "Grid Service is healthy");
} 
elseif ($uri === '/api/grid-quality' && $method === 'POST') {
    $gridController->store();
}
elseif ($uri === '/api/grid-quality' && $method === 'GET') {
    $gridController->index();
}
else {
    sendResponse("error", 404, null, "Endpoint not found");
}
