<?php
namespace App\Controllers;
use App\Database;
use App\Services\RabbitMQPublisher;
use PDO;

class ReportController {
    private PDO $conn;
    private RabbitMQPublisher $publisher;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
        $this->publisher = new RabbitMQPublisher();
    }

    // POST /api/reports
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $headers = apache_request_headers();
        $citizen_id = $headers['X-Citizen-Id'] ?? $data['citizen_id'] ?? null;

        if (!$citizen_id || empty($data['category']) || empty($data['description'])) {
            sendResponse("error", 400, null, "Data laporan tidak lengkap");
        }

        $createdAt = date('Y-m-d H:i:s');
        
        // Save to DB
        $stmt = $this->conn->prepare("INSERT INTO citizen_reports (citizen_id, category, description, zone_id, status, created_at) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([$citizen_id, $data['category'], $data['description'], $data['zone_id'] ?? 1, $createdAt]);
        $reportId = $this->conn->lastInsertId();

        $reportData = [
            'id' => $reportId,
            'citizen_id' => $citizen_id,
            'category' => $data['category'],
            'status' => 'pending'
        ];

        // Trigger event RabbitMQ
        $this->publisher->publish('report.submitted', $reportData);

        sendResponse("success", 201, $reportData, "Laporan berhasil disubmit");
    }

    // PATCH /api/reports/:id/status
    public function updateStatus($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['status'])) {
            sendResponse("error", 400, null, "Status baru harus diisi");
        }

        $stmt = $this->conn->prepare("UPDATE citizen_reports SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $id]);

        sendResponse("success", 200, ["id" => $id, "status" => $data['status']], "Status laporan diperbarui");
    }
}