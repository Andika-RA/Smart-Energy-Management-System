<?php
namespace app\models;
use app\database;
use PDO;

class Report {
    private PDO $conn;
    private string $table = "citizen_reports";

    public function __construct() {
        $this->conn = (new Database())->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO {$this->table} (citizen_id, category, description, zone_id, status, created_at) VALUES (?, ?, ?, ?, 'pending', ?)";
        $stmt = $this->conn->prepare($query);
        $createdAt = date('Y-m-d H:i:s');
        
        $stmt->execute([
            $data['citizen_id'], 
            $data['category'], 
            $data['description'], 
            $data['zone_id'] ?? 1, 
            $createdAt
        ]);
        
        $data['id'] = $this->conn->lastInsertId();
        $data['status'] = 'pending';
        $data['created_at'] = $createdAt;
        return $data;
    }

    public function findAll(): array {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}