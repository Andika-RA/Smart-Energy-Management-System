<?php
namespace app\models;
use app\database;
use PDO;

class report {
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

    public function findAllWithFilter(?string $status, ?int $zone_id): array {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        if ($zone_id) {
            $query .= " AND zone_id = ?";
            $params[] = $zone_id;
        }
        
        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}