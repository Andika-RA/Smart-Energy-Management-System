<?php
// app/controllers/ZoneInfrastructureController.php
namespace app\controllers;
use app\models\ZoneInfrastructure;

class ZoneInfrastructureController {
    private ZoneInfrastructure $model;

    public function __construct() {
        $this->model = new ZoneInfrastructure();
    }

    public function index() {
        try {
            $zones = $this->model->getAll();
            sendResponse("success", 200, $zones, "Data zone infrastructure berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data zone: " . $e->getMessage());
        }
    }

    public function show(int $id) {
        try {
            $zone = $this->model->getById($id);
            if ($zone === null) {
                sendResponse("error", 404, null, "Zone infrastructure dengan ID {$id} tidak ditemukan");
            }
            sendResponse("success", 200, $zone, "Data zone infrastructure berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data zone: " . $e->getMessage());
        }
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['name']) || empty($data['city_district']) || !isset($data['max_capacity_ampere'])) {
            sendResponse("error", 400, null, "Data tidak lengkap. Field 'name', 'city_district', dan 'max_capacity_ampere' wajib diisi");
        }

        if (!is_numeric($data['max_capacity_ampere'])) {
            sendResponse("error", 400, null, "Field 'max_capacity_ampere' harus berupa angka");
        }

        $data['transformer_capacity_kva'] = isset($data['transformer_capacity_kva']) ? (float)$data['transformer_capacity_kva'] : 0.0;
        $data['nominal_voltage'] = isset($data['nominal_voltage']) ? (float)$data['nominal_voltage'] : 220.0;
        $data['area_km2'] = isset($data['area_km2']) ? (float)$data['area_km2'] : null;
        $data['health_status'] = isset($data['health_status']) ? $data['health_status'] : 'normal';

        if (!in_array($data['health_status'], ['normal', 'warning', 'critical'])) {
            sendResponse("error", 400, null, "Nilai 'health_status' tidak valid. Harus salah satu dari: 'normal', 'warning', 'critical'");
        }

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Zone infrastructure berhasil dibuat");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal membuat zone infrastructure: " . $e->getMessage());
        }
    }

    public function update(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Zone infrastructure dengan ID {$id} tidak ditemukan");
            }

            $input = json_decode(file_get_contents("php://input"), true);

            $data = array_merge($existing, $input);

            if (empty($data['name']) || empty($data['city_district']) || !isset($data['max_capacity_ampere'])) {
                sendResponse("error", 400, null, "Data tidak lengkap. Field 'name', 'city_district', dan 'max_capacity_ampere' wajib diisi");
            }

            if (!is_numeric($data['max_capacity_ampere'])) {
                sendResponse("error", 400, null, "Field 'max_capacity_ampere' harus berupa angka");
            }

            if (!in_array($data['health_status'], ['normal', 'warning', 'critical'])) {
                sendResponse("error", 400, null, "Nilai 'health_status' tidak valid. Harus salah satu dari: 'normal', 'warning', 'critical'");
            }

            $this->model->update($id, $data);
            $updated = $this->model->getById($id);
            sendResponse("success", 200, $updated, "Zone infrastructure berhasil diperbarui");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal memperbarui zone infrastructure: " . $e->getMessage());
        }
    }

    public function destroy(int $id) {
        try {
            $existing = $this->model->getById($id);
            if ($existing === null) {
                sendResponse("error", 404, null, "Zone infrastructure dengan ID {$id} tidak ditemukan");
            }

            $this->model->delete($id);
            sendResponse("success", 200, null, "Zone infrastructure berhasil dihapus");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menghapus zone infrastructure: " . $e->getMessage());
        }
    }
}
