<?php
require_once __DIR__ . '/../vendor/autoload.php';

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
        "service" => "citizen-service"
    ]);
    exit;
}

use app\controllers\citizenController;
use app\controllers\reportController;
use app\controllers\notifController;

if ($uri === '/health' && $method === 'GET') {
    sendResponse("success", 200, null, "Citizen Service is healthy");
} 
// Endpoints Citizen
elseif ($uri === '/api/citizens' && $method === 'POST') {
    (new citizenController())->store();
}
elseif (preg_match('#^/api/citizens/(\d+)$#', $uri, $matches) && $method === 'GET') {
    (new citizenController())->show($matches[1]);
}
// Endpoints Laporan 
elseif ($uri === '/api/reports' && $method === 'POST') {
    (new reportController())->store();
} 
elseif ($uri === '/api/reports' && $method === 'GET') {
    (new reportController())->index();
} 
elseif (preg_match('#^/api/reports/(\d+)/status$#', $uri, $matches) && $method === 'PATCH') {
    (new reportController())->updateStatus($matches[1]);
}
// Endpoints Notifikasi 
elseif ($uri === '/api/notifications' && $method === 'GET') {
    (new notifController())->index();
}
else {
    sendResponse("error", 404, null, "Endpoint not found");
}