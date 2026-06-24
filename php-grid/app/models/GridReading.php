<?php
// app/models/GridReading.php
namespace app\models;
use app\database;
use PDO;

class GridReading {
    private PDO $conn;
    private string $table_name = "grid_readings";

    public function __construct() {
        $db = new database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . " 
                  (zone_id, voltage, current, power_factor, recorded_at) 
                  VALUES (:zone_id, :voltage, :current, :power_factor, :recorded_at)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':voltage', $data['voltage']);
        $stmt->bindParam(':current', $data['current']);
        $stmt->bindParam(':power_factor', $data['power_factor']);
        $stmt->bindParam(':recorded_at', $data['recorded_at']);
        
        $stmt->execute();
        $data['id'] = (int)$this->conn->lastInsertId();
        
        return $data;
    }

    public function getAll(?int $zone_id = null): array {
        if ($zone_id !== null) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE zone_id = :zone_id ORDER BY recorded_at DESC LIMIT 100";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY recorded_at DESC LIMIT 100";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }

    public function update(int $id, array $data): bool {
        $query = "UPDATE " . $this->table_name . " SET 
                  zone_id = :zone_id, 
                  voltage = :voltage, 
                  current = :current, 
                  power_factor = :power_factor, 
                  recorded_at = :recorded_at 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':voltage', $data['voltage']);
        $stmt->bindParam(':current', $data['current']);
        $stmt->bindParam(':power_factor', $data['power_factor']);
        $stmt->bindParam(':recorded_at', $data['recorded_at']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
