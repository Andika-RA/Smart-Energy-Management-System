<?php
namespace App\Controllers;
use App\Models\Report;
use App\Services\RabbitMQPublisher;
use App\Validators\ReportValidator;

class reportController {
    private Report $model;
    private RabbitMQPublisher $publisher;

    public function __construct() {
        $this->model = new Report();
        $this->publisher = new RabbitMQPublisher();
    }

   // GET /api/reports
    public function index() {
        $status = $_GET['status'] ?? null;
        $zone_id = $_GET['zone_id'] ?? null;
        
        $reports = $this->model->findAllWithFilter($status, $zone_id ? (int)$zone_id : null);
        sendResponse("success", 200, $reports, "Daftar laporan berhasil diambil");
    }

    // POST /api/reports
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);
        $headers = apache_request_headers();
        $data['citizen_id'] = $headers['X-Citizen-Id'] ?? $data['citizen_id'] ?? 1; 

        try {
            $validated = ReportValidator::validate($data);
            $record = $this->model->create($validated);
            
            $this->publisher->publish('report.submitted', [
                'report_id' => $record['id'],
                'citizen_id' => $record['citizen_id'],
                'category' => $record['category'],
                'timestamp' => $record['created_at']
            ]);

            sendResponse("success", 201, $record, "Laporan berhasil disubmit");
        } catch (\Exception $e) {
            sendResponse("error", 400, null, $e->getMessage());
        }
    }

    // PATCH /api/reports/:id/status
    public function updateStatus($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['status'])) {
            sendResponse("error", 400, null, "Status baru wajib diisi");
        }

        $this->model->updateStatus((int)$id, $data['status']);
        sendResponse("success", 200, ["id" => $id, "status" => $data['status']], "Status laporan diperbarui");
    }
}