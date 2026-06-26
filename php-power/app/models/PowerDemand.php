<?php
// app/models/PowerDemand.php
namespace app\models;
use app\Database;
use PDO;

class PowerDemand {
    private PDO $conn;
    private string $table_name = "power_demands";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . "
                  (zone_id, power_demand_kw, recorded_at)
                  VALUES (:zone_id, :power_demand_kw, :recorded_at)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':power_demand_kw', $data['power_demand_kw']);
        $stmt->bindParam(':recorded_at', $data['recorded_at']);

        $stmt->execute();
        $data['id'] = (int)$this->conn->lastInsertId();

        return $data;
    }

    public function getAll(?int $zone_id = null, ?string $from = null, ?string $to = null): array {
        $query = "SELECT * FROM " . $this->table_name;
        $conditions = [];
        $params = [];

        if ($zone_id !== null) {
            $conditions[] = "zone_id = :zone_id";
            $params[':zone_id'] = $zone_id;
        }
        if ($from !== null) {
            $conditions[] = "recorded_at >= :from";
            $params[':from'] = $from;
        }
        if ($to !== null) {
            $conditions[] = "recorded_at <= :to";
            $params[':to'] = $to;
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY recorded_at DESC LIMIT 100";

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
                  power_demand_kw = :power_demand_kw,
                  recorded_at = :recorded_at
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':power_demand_kw', $data['power_demand_kw']);
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
