<?php
// public/index.php
require_once __DIR__ . '/../vendor/autoload.php';

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Helper function untuk format response standar
function sendResponse($status, $code, $data, $message) {
    http_response_code($code);
    echo json_encode([
        "status" => $status,
        "code" => $code,
        "data" => $data,
        "message" => $message,
        "timestamp" => date("Y-m-d\TH:i:s.000\Z"),
        "service" => "power-service"
    ]);
    exit;
}

// Extract ID from path if present (e.g. /api/power/5)
$id = null;
$parts = explode('/', trim($uri, '/'));
if (count($parts) > 1 && is_numeric(end($parts))) {
    $id = (int)end($parts);
    $baseUri = '/' . implode('/', array_slice($parts, 0, -1));
} else {
    $baseUri = $uri;
    $id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
}

use app\controllers\PowerController;
use app\controllers\WeatherController;
use app\controllers\ForecastController;

// Routing
if ($baseUri === '/health' && $method === 'GET') {
    sendResponse("success", 200, null, "Power Service is healthy");
}
// Power Demand Routes
elseif ($baseUri === '/api/power') {
    $controller = new PowerController();
    if ($method === 'GET') {
        if ($id !== null) {
            $controller->show($id);
        } else {
            $controller->index();
        }
    } elseif ($method === 'POST') {
        $controller->store();
    } elseif (in_array($method, ['PUT', 'PATCH'])) {
        if ($id !== null) {
            $controller->update($id);
        } else {
            sendResponse("error", 400, null, "ID power demand wajib disertakan untuk pembaruan");
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $controller->destroy($id);
        } else {
            sendResponse("error", 400, null, "ID power demand wajib disertakan untuk penghapusan");
        }
    } else {
        sendResponse("error", 405, null, "Metode HTTP tidak diizinkan");
    }
}
// Weather Log Routes
elseif ($baseUri === '/api/weather') {
    $controller = new WeatherController();
    if ($method === 'GET') {
        if ($id !== null) {
            $controller->show($id);
        } else {
            $controller->index();
        }
    } elseif ($method === 'POST') {
        $controller->store();
    } elseif (in_array($method, ['PUT', 'PATCH'])) {
        if ($id !== null) {
            $controller->update($id);
        } else {
            sendResponse("error", 400, null, "ID data cuaca wajib disertakan untuk pembaruan");
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $controller->destroy($id);
        } else {
            sendResponse("error", 400, null, "ID data cuaca wajib disertakan untuk penghapusan");
        }
    } else {
        sendResponse("error", 405, null, "Metode HTTP tidak diizinkan");
    }
}
// Forecast Routes
elseif ($baseUri === '/api/forecast') {
    $controller = new ForecastController();
    if ($method === 'GET') {
        if ($id !== null) {
            $controller->show($id);
        } else {
            $controller->index();
        }
    } elseif ($method === 'POST') {
        $controller->store();
    } elseif (in_array($method, ['PUT', 'PATCH'])) {
        if ($id !== null) {
            $controller->update($id);
        } else {
            sendResponse("error", 400, null, "ID forecast wajib disertakan untuk pembaruan");
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $controller->destroy($id);
        } else {
            sendResponse("error", 400, null, "ID forecast wajib disertakan untuk penghapusan");
        }
    } else {
        sendResponse("error", 405, null, "Metode HTTP tidak diizinkan");
    }
}
// Endpoint not found
else {
    sendResponse("error", 404, null, "Endpoint not found");
}
