<?php
// app/models/ZoneInfrastructure.php
namespace app\models;
use app\Database;
use PDO;

/**
 * Model baca-saja untuk tabel shared_zones.
 * Power Service hanya membaca data zona (untuk validasi zone_id & ambang batas
 * kapasitas), bukan pemilik data zona. CRUD zona ditangani oleh Grid Service.
 */
class ZoneInfrastructure {
    private PDO $conn;
    private string $table_name = "shared_zones";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }
}
