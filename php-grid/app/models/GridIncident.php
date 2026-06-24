<?php
// app/models/GridIncident.php
namespace app\models;
use app\database;
use PDO;

class GridIncident {
    private PDO $conn;
    private string $table_name = "grid_incidents";

    public function __construct() {
        $db = new database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . " 
                  (zone_id, type, severity, status, description, resolved_at, reported_at) 
                  VALUES (:zone_id, :type, :severity, :status, :description, :resolved_at, :reported_at)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':severity', $data['severity']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':resolved_at', $data['resolved_at']);
        $stmt->bindParam(':reported_at', $data['reported_at']);
        
        $stmt->execute();
        $data['id'] = (int)$this->conn->lastInsertId();
        
        return $data;
    }

    public function getAll(?int $zone_id = null, ?string $status = null): array {
        $query = "SELECT * FROM " . $this->table_name;
        $conditions = [];
        $params = [];

        if ($zone_id !== null) {
            $conditions[] = "zone_id = :zone_id";
            $params[':zone_id'] = $zone_id;
        }

        if ($status !== null) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY reported_at DESC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => &$val) {
            if ($key === ':zone_id') {
                $stmt->bindParam($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindParam($key, $val);
            }
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
                  type = :type, 
                  severity = :severity, 
                  status = :status, 
                  description = :description, 
                  resolved_at = :resolved_at, 
                  reported_at = :reported_at 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':severity', $data['severity']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':resolved_at', $data['resolved_at']);
        $stmt->bindParam(':reported_at', $data['reported_at']);
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
