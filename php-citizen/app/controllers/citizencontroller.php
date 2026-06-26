<?php
// app/controllers/CitizenController.php
namespace app\controllers;

use app\models\Citizen;
use app\validators\CitizenValidator;

class CitizenController {
    private Citizen $model;

    public function __construct() {
        $this->model = new Citizen();
    }

    // POST /api/citizens
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            $validatedData = CitizenValidator::validate($data);
            $validatedData['created_at'] = date('Y-m-d H:i:s');
            $validatedData['role'] = 'resident';

            $record = $this->model->create($validatedData);
            sendResponse("success", 201, $record, "Warga berhasil didaftarkan");
        } catch (\Exception $e) {
            sendResponse("error", 400, null, $e->getMessage());
        }
    }

    // GET /api/citizens/:id
    public function show($id) {
        $citizen = $this->model->findById((int)$id);
        if (!$citizen) {
            sendResponse("error", 404, null, "Warga tidak ditemukan");
        }
        sendResponse("success", 200, $citizen, "Data profil warga");
    }
}
