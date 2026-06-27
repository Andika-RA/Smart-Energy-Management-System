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
        "service" => "grid-service"
    ]);
    exit;
}

// Extract ID from path if present (e.g. /api/zones/5)
$id = null;
$parts = explode('/', trim($uri, '/'));
if (count($parts) > 1 && is_numeric(end($parts))) {
    $id = (int)end($parts);
    $baseUri = '/' . implode('/', array_slice($parts, 0, -1));
} else {
    $baseUri = $uri;
    $id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
}

use app\controllers\ZoneInfrastructureController;
use app\controllers\GridReadingController;
use app\controllers\GridIncidentController;

$zoneController = new ZoneInfrastructureController();
$readingController = new GridReadingController();
$incidentController = new GridIncidentController();

// Routing
if ($baseUri === '/health' && $method === 'GET') {
    try {
        $db = new \app\Database();
        $conn = $db->getConnection();
        $conn->query("SELECT 1");
        sendResponse("success", 200, ["database" => "connected"], "Grid Service is healthy");
    } catch (\Exception $e) {
        sendResponse("error", 500, ["database" => "disconnected", "error" => $e->getMessage()], "Grid Service is unhealthy");
    }
}
// Zone Routes
elseif ($baseUri === '/api/zones' || $baseUri === '/api/zone') {
    if ($method === 'GET') {
        if ($id !== null) {
            $zoneController->show($id);
        } else {
            $zoneController->index();
        }
    } elseif ($method === 'POST') {
        $zoneController->store();
    } elseif (in_array($method, ['PUT', 'PATCH'])) {
        if ($id !== null) {
            $zoneController->update($id);
        } else {
            sendResponse("error", 400, null, "ID wilayah wajib disertakan untuk pembaruan");
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $zoneController->destroy($id);
        } else {
            sendResponse("error", 400, null, "ID wilayah wajib disertakan untuk penghapusan");
        }
    } else {
        sendResponse("error", 405, null, "Metode HTTP tidak diizinkan");
    }
}
// Grid Readings / Quality Routes
elseif ($baseUri === '/api/grid-readings' || $baseUri === '/api/grid-quality') {
    if ($method === 'GET') {
        if ($id !== null) {
            $readingController->show($id);
        } else {
            $readingController->index();
        }
    } elseif ($method === 'POST') {
        $readingController->store();
    } elseif (in_array($method, ['PUT', 'PATCH'])) {
        if ($id !== null) {
            $readingController->update($id);
        } else {
            sendResponse("error", 400, null, "ID grid reading wajib disertakan untuk pembaruan");
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $readingController->destroy($id);
        } else {
            sendResponse("error", 400, null, "ID grid reading wajib disertakan untuk penghapusan");
        }
    } else {
        sendResponse("error", 405, null, "Metode HTTP tidak diizinkan");
    }
}
// Grid Incident Routes
elseif ($baseUri === '/api/grid-incidents' || $baseUri === '/api/grid-incident') {
    if ($method === 'GET') {
        if ($id !== null) {
            $incidentController->show($id);
        } else {
            $incidentController->index();
        }
    } elseif ($method === 'POST') {
        $incidentController->store();
    } elseif (in_array($method, ['PUT', 'PATCH'])) {
        if ($id !== null) {
            $incidentController->update($id);
        } else {
            sendResponse("error", 400, null, "ID incident wajib disertakan untuk pembaruan");
        }
    } elseif ($method === 'DELETE') {
        if ($id !== null) {
            $incidentController->destroy($id);
        } else {
            sendResponse("error", 400, null, "ID incident wajib disertakan untuk penghapusan");
        }
    } else {
        sendResponse("error", 405, null, "Metode HTTP tidak diizinkan");
    }
}
// Endpoint not found
else {
    sendResponse("error", 404, null, "Endpoint not found");
}
