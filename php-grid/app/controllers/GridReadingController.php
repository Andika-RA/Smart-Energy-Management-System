<?php
// app/controllers/GridReadingController.php
namespace app\controllers;
use app\models\GridReading;
use app\models\ZoneInfrastructure;

class GridReadingController {
    private GridReading $model;
    private ZoneInfrastructure $zoneModel;

    public function __construct() {
        $this->model = new GridReading();
        $this->zoneModel = new ZoneInfrastructure();
    }

    public function index() {
        try {
            $zone_id = isset($_GET['zone_id']) ? (int)$_GET['zone_id'] : null;
            $readings = $this->model->getAll($zone_id);
            sendResponse("success", 200, $readings, "Data grid readings berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data grid reading: " . $e->getMessage());
        }
    }

    public function show(int $id) {
        try {
            $reading = $this->model->getById($id);
            if ($reading === null) {
                sendResponse("error", 404, null, "Grid reading dengan ID {$id} tidak ditemukan");
            }
            sendResponse("success", 200, $reading, "Data grid reading berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data grid reading: " . $e->getMessage());
        }
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validation for required fields
        if (!isset($data['zone_id']) || !isset($data['voltage']) || !isset($data['current']) || !isset($data['power_factor'])) {
            sendResponse("error", 400, null, "Data tidak lengkap. Field 'zone_id', 'voltage', 'current', dan 'power_factor' wajib diisi");
        }

        // Validate range limits (Fix 8)
        if ($data['voltage'] < 0 || $data['voltage'] > 260) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Batas tegangan (voltage) harus di antara 0 sampai 260 V");
        }
        if ($data['current'] < 0 || $data['current'] > 500) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Batas arus (current) harus di antara 0 sampai 500 A");
        }
        if ($data['power_factor'] < 0.0 || $data['power_factor'] > 1.0) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Batas faktor daya (power_factor) harus di antara 0.0 sampai 1.0");
        }

        // Validate zone exists
        $zone = $this->zoneModel->getById((int)$data['zone_id']);
        if ($zone === null) {
            sendResponse("error", 400, null, "Gagal menyimpan data: Zone ID {$data['zone_id']} tidak ditemukan");
        }

        if (!isset($data['recorded_at'])) {
            $data['recorded_at'] = date('Y-m-d H:i:s');
        }

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Data grid reading berhasil disimpan");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        }
    }

    public function update(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Grid reading dengan ID {$id} tidak ditemukan");
            }

            $input = json_decode(file_get_contents("php://input"), true);
            $data = array_merge($existing, $input);

            // Validate range limits (Fix 8)
            if ($data['voltage'] < 0 || $data['voltage'] > 260) {
                sendResponse("error", 400, null, "Gagal menyimpan data: Batas tegangan (voltage) harus di antara 0 sampai 260 V");
            }
            if ($data['current'] < 0 || $data['current'] > 500) {
                sendResponse("error", 400, null, "Gagal menyimpan data: Batas arus (current) harus di antara 0 sampai 500 A");
            }
            if ($data['power_factor'] < 0.0 || $data['power_factor'] > 1.0) {
                sendResponse("error", 400, null, "Gagal menyimpan data: Batas faktor daya (power_factor) harus di antara 0.0 sampai 1.0");
            }

            // Validate zone exists
            $zone = $this->zoneModel->getById((int)$data['zone_id']);
            if ($zone === null) {
                sendResponse("error", 400, null, "Gagal menyimpan data: Zone ID {$data['zone_id']} tidak ditemukan");
            }

            $this->model->update($id, $data);
            $updated = $this->model->getById($id);
            sendResponse("success", 200, $updated, "Grid reading berhasil diperbarui");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal memperbarui grid reading: " . $e->getMessage());
        }
    }

    public function destroy(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Grid reading dengan ID {$id} tidak ditemukan");
            }

            $this->model->delete($id);
            sendResponse("success", 200, null, "Grid reading berhasil dihapus");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menghapus grid reading: " . $e->getMessage());
        }
    }
}
