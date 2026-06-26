<?php
// app/models/citizen.php
namespace App\models;
use App\database;
use PDO;

class citizen {
    private PDO $conn;
    private string $table_name = "citizen_citizens"; // Prefix sesuai best practice dokumen

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nik, name, email, phone, zone_id, role, created_at) 
                  VALUES (:nik, :name, :email, :phone, :zone_id, :role, :created_at)";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameter
        $stmt->bindParam(':nik', $data['nik']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':zone_id', $data['zone_id']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':created_at', $data['created_at']);
        
        $stmt->execute();
        $data['id'] = $this->conn->lastInsertId();
        
        return $data;
    }
}