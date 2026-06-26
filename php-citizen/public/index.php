<?php
require_once __DIR__ . '/../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Citizen-Id");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

use app\controllers\CitizenController;
use app\controllers\ReportController;
use app\controllers\NotifController;

if ($uri === '/health' && $method === 'GET') {
    sendResponse("success", 200, null, "Citizen Service is healthy");
}
// Endpoints Citizen
elseif ($uri === '/api/citizens' && $method === 'POST') {
    (new CitizenController())->store();
}
elseif (preg_match('#^/api/citizens/(\d+)$#', $uri, $matches) && $method === 'GET') {
    (new CitizenController())->show($matches[1]);
}
// Endpoints Laporan
elseif ($uri === '/api/reports' && $method === 'POST') {
    (new ReportController())->store();
}
elseif ($uri === '/api/reports' && $method === 'GET') {
    (new ReportController())->index();
}
elseif (preg_match('#^/api/reports/(\d+)/status$#', $uri, $matches) && $method === 'PATCH') {
    (new ReportController())->updateStatus($matches[1]);
}
// Endpoints Notifikasi
elseif ($uri === '/api/notifications' && $method === 'GET') {
    (new NotifController())->index();
}
else {
    sendResponse("error", 404, null, "Endpoint not found");
}
