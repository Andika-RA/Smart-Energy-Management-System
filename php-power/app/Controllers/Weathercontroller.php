<?php
// app/controllers/WeatherController.php
namespace app\controllers;

use app\models\WeatherLog;
use app\models\ZoneInfrastructure;

class WeatherController {
    private WeatherLog $model;
    private ZoneInfrastructure $zoneModel;

    public function __construct() {
        $this->model = new WeatherLog();
        $this->zoneModel = new ZoneInfrastructure();
    }

    // GET /api/weather?zone_id=
    public function index() {
        try {
            $zone_id = isset($_GET['zone_id']) ? (int)$_GET['zone_id'] : null;
            $logs = $this->model->getAll($zone_id);
            sendResponse("success", 200, $logs, "Data cuaca berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data cuaca: " . $e->getMessage());
        }
    }

    // GET /api/weather/:id
    public function show(int $id) {
        try {
            $log = $this->model->getById($id);
            if ($log === null) {
                sendResponse("error", 404, null, "Data cuaca dengan ID {$id} tidak ditemukan");
            }
            sendResponse("success", 200, $log, "Data cuaca berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data cuaca: " . $e->getMessage());
        }
    }

    // POST /api/weather
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['zone_id']) || !isset($data['temperature']) || !isset($data['humidity'])) {
            sendResponse("error", 400, null, "Data tidak lengkap. Field 'zone_id', 'temperature', dan 'humidity' wajib diisi");
        }

        if ($data['humidity'] < 0 || $data['humidity'] > 100) {
            sendResponse("error", 400, null, "Field 'humidity' harus di antara 0 sampai 100");
        }

        $zone = $this->zoneModel->getById((int)$data['zone_id']);
        if ($zone === null) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Zone ID {$data['zone_id']} tidak ditemukan");
        }

        if (!isset($data['recorded_at'])) {
            $data['recorded_at'] = date('Y-m-d H:i:s');
        }

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Data cuaca berhasil disimpan");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        }
    }

    // PUT/PATCH /api/weather/:id
    public function update(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Data cuaca dengan ID {$id} tidak ditemukan");
            }

            $input = json_decode(file_get_contents("php://input"), true);
            $data = array_merge($existing, $input);

            if ($data['humidity'] < 0 || $data['humidity'] > 100) {
                sendResponse("error", 400, null, "Field 'humidity' harus di antara 0 sampai 100");
            }

            $zone = $this->zoneModel->getById((int)$data['zone_id']);
            if ($zone === null) {
                sendResponse("error", 400, null, "Gagal memperbarui data: Zone ID {$data['zone_id']} tidak ditemukan");
            }

            $this->model->update($id, $data);
            $updated = $this->model->getById($id);
            sendResponse("success", 200, $updated, "Data cuaca berhasil diperbarui");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal memperbarui data cuaca: " . $e->getMessage());
        }
    }

    // DELETE /api/weather/:id
    public function destroy(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Data cuaca dengan ID {$id} tidak ditemukan");
            }

            $this->model->delete($id);
            sendResponse("success", 200, null, "Data cuaca berhasil dihapus");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menghapus data cuaca: " . $e->getMessage());
        }
    }
}
