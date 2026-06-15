<?php
// app/models/grid.php
namespace app\models;
use app\database;
use PDO;

class grid {
    private PDO $conn;
    private string $table_name = "grid_quality";

    public function __construct() {
        $db = new database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . " 
                  (voltage, current, power_factor, temperature, humidity, zone, grid_status, timestamp) 
                  VALUES (:voltage, :current, :power_factor, :temperature, :humidity, :zone, :grid_status, :timestamp)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':voltage', $data['voltage']);
        $stmt->bindParam(':current', $data['current']);
        $stmt->bindParam(':power_factor', $data['power_factor']);
        $stmt->bindParam(':temperature', $data['temperature']);
        $stmt->bindParam(':humidity', $data['humidity']);
        $stmt->bindParam(':zone', $data['zone']);
        $stmt->bindParam(':grid_status', $data['grid_status']);
        $stmt->bindParam(':timestamp', $data['timestamp']);
        
        $stmt->execute();
        $data['id'] = $this->conn->lastInsertId();
        
        return $data;
    }

    public function getAll(): array {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY timestamp DESC LIMIT 100";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
