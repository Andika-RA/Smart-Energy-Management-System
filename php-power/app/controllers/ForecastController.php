<?php
// app/controllers/ForecastController.php
namespace app\controllers;

use app\models\Forecast;
use app\models\ZoneInfrastructure;
use PDOException;

class ForecastController {
    private Forecast $model;
    private ZoneInfrastructure $zoneModel;
    private array $allowedStatus = ['Lancar', 'Sedang', 'Padat'];

    public function __construct() {
        $this->model = new Forecast();
        $this->zoneModel = new ZoneInfrastructure();
    }

    // GET /api/forecast?zone_id=
    public function index() {
        try {
            $zone_id = isset($_GET['zone_id']) ? (int)$_GET['zone_id'] : null;
            $forecasts = $this->model->getAll($zone_id);
            sendResponse("success", 200, $forecasts, "Data forecast berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data forecast: " . $e->getMessage());
        }
    }

    // GET /api/forecast/:id
    public function show(int $id) {
        try {
            $forecast = $this->model->getById($id);
            if ($forecast === null) {
                sendResponse("error", 404, null, "Forecast dengan ID {$id} tidak ditemukan");
            }
            sendResponse("success", 200, $forecast, "Data forecast berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data forecast: " . $e->getMessage());
        }
    }

    // POST /api/forecast
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['zone_id']) || !isset($data['predicted_demand_kw']) || empty($data['status_level']) || empty($data['forecast_for_time'])) {
            sendResponse("error", 400, null, "Data tidak lengkap. Field 'zone_id', 'predicted_demand_kw', 'status_level', dan 'forecast_for_time' wajib diisi");
        }

        if (!in_array($data['status_level'], $this->allowedStatus)) {
            sendResponse("error", 400, null, "Nilai 'status_level' tidak valid. Harus salah satu dari: " . implode(', ', $this->allowedStatus));
        }

        $zone = $this->zoneModel->getById((int)$data['zone_id']);
        if ($zone === null) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Zone ID {$data['zone_id']} tidak ditemukan");
        }

        $data['model_version'] = $data['model_version'] ?? 'RandomForest_v1.0';
        $data['generated_from'] = $data['generated_from'] ?? null;

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Forecast berhasil disimpan");
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                sendResponse("error", 409, null, "Forecast untuk zone_id {$data['zone_id']} pada waktu tersebut sudah ada");
            }
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        }
    }

    // PUT/PATCH /api/forecast/:id
    public function update(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Forecast dengan ID {$id} tidak ditemukan");
            }

            $input = json_decode(file_get_contents("php://input"), true);
            $data = array_merge($existing, $input);

            if (!in_array($data['status_level'], $this->allowedStatus)) {
                sendResponse("error", 400, null, "Nilai 'status_level' tidak valid. Harus salah satu dari: " . implode(', ', $this->allowedStatus));
            }

            $zone = $this->zoneModel->getById((int)$data['zone_id']);
            if ($zone === null) {
                sendResponse("error", 400, null, "Gagal memperbarui data: Zone ID {$data['zone_id']} tidak ditemukan");
            }

            $this->model->update($id, $data);
            $updated = $this->model->getById($id);
            sendResponse("success", 200, $updated, "Forecast berhasil diperbarui");
        } catch (PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                sendResponse("error", 409, null, "Forecast untuk zone_id tersebut pada waktu itu sudah ada");
            }
            sendResponse("error", 500, null, "Gagal memperbarui forecast: " . $e->getMessage());
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal memperbarui forecast: " . $e->getMessage());
        }
    }

    // DELETE /api/forecast/:id
    public function destroy(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Forecast dengan ID {$id} tidak ditemukan");
            }

            $this->model->delete($id);
            sendResponse("success", 200, null, "Forecast berhasil dihapus");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menghapus forecast: " . $e->getMessage());
        }
    }
}
