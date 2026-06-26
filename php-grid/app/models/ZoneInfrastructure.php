<?php
// app/models/ZoneInfrastructure.php
namespace app\models;
use app\Database;
use PDO;

class ZoneInfrastructure {
    private PDO $conn;
    private string $table_name = "shared_zones";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . "
                  (name, city_district, max_capacity_ampere, transformer_capacity_kva, nominal_voltage, area_km2, health_status)
                  VALUES (:name, :city_district, :max_capacity_ampere, :transformer_capacity_kva, :nominal_voltage, :area_km2, :health_status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':city_district', $data['city_district']);
        $stmt->bindParam(':max_capacity_ampere', $data['max_capacity_ampere']);
        $stmt->bindParam(':transformer_capacity_kva', $data['transformer_capacity_kva']);
        $stmt->bindParam(':nominal_voltage', $data['nominal_voltage']);
        $stmt->bindParam(':area_km2', $data['area_km2']);
        $stmt->bindParam(':health_status', $data['health_status']);

        $stmt->execute();
        $data['id'] = (int)$this->conn->lastInsertId();

        return $data;
    }

    public function getAll(): array {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
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
                  name = :name,
                  city_district = :city_district,
                  max_capacity_ampere = :max_capacity_ampere,
                  transformer_capacity_kva = :transformer_capacity_kva,
                  nominal_voltage = :nominal_voltage,
                  area_km2 = :area_km2,
                  health_status = :health_status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':city_district', $data['city_district']);
        $stmt->bindParam(':max_capacity_ampere', $data['max_capacity_ampere']);
        $stmt->bindParam(':transformer_capacity_kva', $data['transformer_capacity_kva']);
        $stmt->bindParam(':nominal_voltage', $data['nominal_voltage']);
        $stmt->bindParam(':area_km2', $data['area_km2']);
        $stmt->bindParam(':health_status', $data['health_status']);
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
