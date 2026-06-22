<?php
namespace app\models;
use app\database;
use PDO;

class notification {
    private PDO $conn;
    
    public function __construct() {
        $this->conn = (new database())->getConnection();
    }

    public function findByCitizenId(int $citizenId): array {
        $stmt = $this->conn->prepare("SELECT * FROM citizen_notifications WHERE citizen_id = ? ORDER BY created_at DESC");
        $stmt->execute([$citizenId]);
        return $stmt->fetchAll();
    }
}