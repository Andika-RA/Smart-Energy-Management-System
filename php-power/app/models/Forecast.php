<?php
// app/models/Forecast.php
namespace app\models;
use app\Database;
use PDO;

class Forecast {
    private PDO $conn;
    private string $table_name = "power_forecasts";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(array $data): array {
        $query = "INSERT INTO " . $this->table_name . "
                  (zone_id, predicted_demand_kw, status_level, forecast_for_time, model_version, generated_from)
                  VALUES (:zone_id, :predicted_demand_kw, :status_level, :forecast_for_time, :model_version, :generated_from)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':predicted_demand_kw', $data['predicted_demand_kw']);
        $stmt->bindParam(':status_level', $data['status_level']);
        $stmt->bindParam(':forecast_for_time', $data['forecast_for_time']);
        $stmt->bindParam(':model_version', $data['model_version']);
        $stmt->bindParam(':generated_from', $data['generated_from']);

        $stmt->execute();
        $data['id'] = (int)$this->conn->lastInsertId();

        return $data;
    }

    public function getAll(?int $zone_id = null): array {
        if ($zone_id !== null) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE zone_id = :zone_id ORDER BY forecast_for_time DESC LIMIT 100";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY forecast_for_time DESC LIMIT 100";
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
                  predicted_demand_kw = :predicted_demand_kw,
                  status_level = :status_level,
                  forecast_for_time = :forecast_for_time,
                  model_version = :model_version,
                  generated_from = :generated_from
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':zone_id', $data['zone_id'], PDO::PARAM_INT);
        $stmt->bindParam(':predicted_demand_kw', $data['predicted_demand_kw']);
        $stmt->bindParam(':status_level', $data['status_level']);
        $stmt->bindParam(':forecast_for_time', $data['forecast_for_time']);
        $stmt->bindParam(':model_version', $data['model_version']);
        $stmt->bindParam(':generated_from', $data['generated_from']);
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
