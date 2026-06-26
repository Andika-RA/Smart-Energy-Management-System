<?php
// app/controllers/PowerController.php
namespace app\controllers;

use app\models\PowerDemand;
use app\models\ZoneInfrastructure;
use app\services\RabbitMQPublisher;

class PowerController {
    private PowerDemand $model;
    private ZoneInfrastructure $zoneModel;
    private RabbitMQPublisher $publisher;

    public function __construct() {
        $this->model = new PowerDemand();
        $this->zoneModel = new ZoneInfrastructure();
        $this->publisher = new RabbitMQPublisher();
    }

    // GET /api/power?zone_id=&from=&to=
    public function index() {
        try {
            $zone_id = isset($_GET['zone_id']) ? (int)$_GET['zone_id'] : null;
            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            $readings = $this->model->getAll($zone_id, $from, $to);
            sendResponse("success", 200, $readings, "Data power demand berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data power demand: " . $e->getMessage());
        }
    }

    // GET /api/power/:id
    public function show(int $id) {
        try {
            $reading = $this->model->getById($id);
            if ($reading === null) {
                sendResponse("error", 404, null, "Power demand dengan ID {$id} tidak ditemukan");
            }
            sendResponse("success", 200, $reading, "Data power demand berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data power demand: " . $e->getMessage());
        }
    }

    // POST /api/power
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['zone_id']) || !isset($data['power_demand_kw'])) {
            sendResponse("error", 400, null, "Data tidak lengkap. Field 'zone_id' dan 'power_demand_kw' wajib diisi");
        }

        if (!is_numeric($data['power_demand_kw']) || $data['power_demand_kw'] < 0) {
            sendResponse("error", 400, null, "Field 'power_demand_kw' harus berupa angka positif");
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

            // Jika konsumsi melebihi kapasitas trafo zona, publish peringatan ke
            // exchange 'city.events' (routing key 'anomaly.alert') agar citizen-service
            // (yang sudah punya consumer untuk topik ini) otomatis mengirim notifikasi.
            $thresholdKw = (float)($zone['transformer_capacity_kva'] ?? 0);
            if ($thresholdKw > 0 && (float)$data['power_demand_kw'] > $thresholdKw) {
                $this->publisher->publish('anomaly.alert', [
                    'zone_id' => (int)$data['zone_id'],
                    'anomaly_score' => round($data['power_demand_kw'] / $thresholdKw, 2),
                    'source' => 'power-service',
                    'power_demand_kw' => $data['power_demand_kw'],
                    'threshold_kw' => $thresholdKw,
                ]);
            }

            sendResponse("success", 201, $record, "Data power demand berhasil disimpan");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        }
    }

    // PUT/PATCH /api/power/:id
    public function update(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Power demand dengan ID {$id} tidak ditemukan");
            }

            $input = json_decode(file_get_contents("php://input"), true);
            $data = array_merge($existing, $input);

            if (!is_numeric($data['power_demand_kw']) || $data['power_demand_kw'] < 0) {
                sendResponse("error", 400, null, "Field 'power_demand_kw' harus berupa angka positif");
            }

            $zone = $this->zoneModel->getById((int)$data['zone_id']);
            if ($zone === null) {
                sendResponse("error", 400, null, "Gagal memperbarui data: Zone ID {$data['zone_id']} tidak ditemukan");
            }

            $this->model->update($id, $data);
            $updated = $this->model->getById($id);
            sendResponse("success", 200, $updated, "Power demand berhasil diperbarui");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal memperbarui power demand: " . $e->getMessage());
        }
    }

    // DELETE /api/power/:id
    public function destroy(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Power demand dengan ID {$id} tidak ditemukan");
            }

            $this->model->delete($id);
            sendResponse("success", 200, null, "Power demand berhasil dihapus");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menghapus power demand: " . $e->getMessage());
        }
    }
}
