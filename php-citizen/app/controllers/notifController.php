<?php
namespace app\controllers;
use app\models\notification;

class notifController {
    private Notification $model;

    public function __construct() {
        $this->model = new Notification();
    }

    // GET /api/notifications
    public function index() {
        $headers = apache_request_headers();
        $citizen_id = $headers['X-Citizen-Id'] ?? 1; 

        $notifs = $this->model->findByCitizenId((int)$citizen_id);
        sendResponse("success", 200, $notifs, "Daftar notifikasi warga");
    }
}