<?php
// app/controllers/GridIncidentController.php
namespace app\controllers;
use app\models\GridIncident;
use app\models\ZoneInfrastructure;

class GridIncidentController {
    private GridIncident $model;
    private ZoneInfrastructure $zoneModel;

    public function __construct() {
        $this->model = new GridIncident();
        $this->zoneModel = new ZoneInfrastructure();
    }

    public function index() {
        try {
            $zone_id = isset($_GET['zone_id']) ? (int)$_GET['zone_id'] : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $incidents = $this->model->getAll($zone_id, $status);
            sendResponse("success", 200, $incidents, "Data grid incidents berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data grid incident: " . $e->getMessage());
        }
    }

    public function show(int $id) {
        try {
            $incident = $this->model->getById($id);
            if ($incident === null) {
                sendResponse("error", 404, null, "Grid incident dengan ID {$id} tidak ditemukan");
            }
            sendResponse("success", 200, $incident, "Data grid incident berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data grid incident: " . $e->getMessage());
        }
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['zone_id']) || empty($data['type']) || empty($data['severity']) || empty($data['description'])) {
            sendResponse("error", 400, null, "Data tidak lengkap. Field 'zone_id', 'type', 'severity', dan 'description' wajib diisi");
        }

        if (!in_array($data['severity'], ['low', 'medium', 'high', 'critical'])) {
            sendResponse("error", 400, null, "Nilai 'severity' tidak valid. Harus salah satu dari: 'low', 'medium', 'high', 'critical'");
        }

        $data['status'] = isset($data['status']) ? $data['status'] : 'open';
        if (!in_array($data['status'], ['open', 'investigating', 'resolved'])) {
            sendResponse("error", 400, null, "Nilai 'status' tidak valid. Harus salah satu dari: 'open', 'investigating', 'resolved'");
        }

        $zone = $this->zoneModel->getById((int)$data['zone_id']);
        if ($zone === null) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Zone ID {$data['zone_id']} tidak ditemukan");
        }

        if (!isset($data['reported_at'])) {
            $data['reported_at'] = date('Y-m-d H:i:s');
        }

        if ($data['status'] === 'resolved') {
            $data['resolved_at'] = isset($data['resolved_at']) ? $data['resolved_at'] : date('Y-m-d H:i:s');
        } else {
            $data['resolved_at'] = null;
        }

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Data grid incident berhasil disimpan");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        }
    }

    public function update(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Grid incident dengan ID {$id} tidak ditemukan");
            }

            $input = json_decode(file_get_contents("php://input"), true);
            $data = array_merge($existing, $input);

            if (!in_array($data['severity'], ['low', 'medium', 'high', 'critical'])) {
                sendResponse("error", 400, null, "Nilai 'severity' tidak valid. Harus salah satu dari: 'low', 'medium', 'high', 'critical'");
            }
            if (!in_array($data['status'], ['open', 'investigating', 'resolved'])) {
                sendResponse("error", 400, null, "Nilai 'status' tidak valid. Harus salah satu dari: 'open', 'investigating', 'resolved'");
            }

            $zone = $this->zoneModel->getById((int)$data['zone_id']);
            if ($zone === null) {
                sendResponse("error", 400, null, "Gagal menyimpan data: Zone ID {$data['zone_id']} tidak ditemukan");
            }

            if ($data['status'] === 'resolved') {
                $data['resolved_at'] = ($existing['status'] !== 'resolved') ? date('Y-m-d H:i:s') : $data['resolved_at'];
            } else {
                $data['resolved_at'] = null;
            }

            $this->model->update($id, $data);
            $updated = $this->model->getById($id);
            sendResponse("success", 200, $updated, "Grid incident berhasil diperbarui");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal memperbarui grid incident: " . $e->getMessage());
        }
    }

    public function destroy(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Grid incident dengan ID {$id} tidak ditemukan");
            }

            $this->model->delete($id);
            sendResponse("success", 200, null, "Grid incident berhasil dihapus");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menghapus grid incident: " . $e->getMessage());
        }
    }
}
