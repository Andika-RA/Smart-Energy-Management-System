<?php
// app/controllers/citizencontroller.php
namespace app\controllers;
use app\models\citizen;

class citizencontroller {
    private citizen $model;

    public function __construct() {
        $this->model = new citizen();
    }

    public function store() {
        // Ambil data JSON dari body request
        $data = json_decode(file_get_contents("php://input"), true);

        // TODO: Tambahkan App\Validators\CitizenValidator di sini nantinya
        if(empty($data['nik']) || empty($data['name']) || empty($data['email'])) {
            sendResponse("error", 400, null, "Data tidak lengkap");
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['role'] = 'warga'; // Default role

        try {
            $record = $this->model->create($data);
            sendResponse("success", 201, $record, "Warga berhasil didaftarkan");
        } catch (\Exception $e) {
            sendResponse("error", 500, null, "Gagal mendaftarkan warga: " . $e->getMessage());
        }
    }
}