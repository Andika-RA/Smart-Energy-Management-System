<?php
// app/controllers/gridcontroller.php
namespace app\controllers;
use app\models\grid;

class gridcontroller {
    private grid $model;

    public function __construct() {
        $this->model = new grid();
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!isset($data['voltage']) || !isset($data['zone'])) {
            sendResponse("error", 400, null, "Data tidak lengkap");
        }

        if(!isset($data['timestamp'])) {
            $data['timestamp'] = date('Y-m-d\TH:i:s\Z');
        }

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Data grid quality berhasil disimpan");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal menyimpan data: " . $e->getMessage());
        }
    }

    public function index() {
        try {
            $records = $this->model->getAll();
            sendResponse("success", 200, $records, "Data grid quality berhasil diambil");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mengambil data: " . $e->getMessage());
        }
    }
}
