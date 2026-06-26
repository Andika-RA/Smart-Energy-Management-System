<?php
// app/models/Notification.php
namespace app\models;

use app\Database;
use PDO;

class Notification {
    private PDO $conn;

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function findByCitizenId(int $citizenId): array {
        $stmt = $this->conn->prepare("SELECT * FROM citizen_notifications WHERE citizen_id = ? ORDER BY created_at DESC");
        $stmt->execute([$citizenId]);
        return $stmt->fetchAll();
    }
}
