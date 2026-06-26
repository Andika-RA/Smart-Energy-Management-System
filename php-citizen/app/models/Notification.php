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
        $query = "SELECT * FROM citizen_notifications 
                  WHERE citizen_id = ? 
                  OR is_broadcast = TRUE 
                  OR citizen_id IS NULL 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$citizenId]);
        return $stmt->fetchAll();
    }
}
