<?php
// app/database.php
namespace app;
use PDO;
use PDOException;

class database {
    private ?PDO $connection = null;

    public function getConnection(): PDO {
        if ($this->connection === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $db_name = getenv('DB_NAME') ?: 'smartcity';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: 'rootpass';

            try {
                $this->connection = new PDO(
                    "mysql:host={$host};dbname={$db_name}", 
                    $username, 
                    $password
                );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $exception) {
                echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
                exit;
            }
        }
        return $this->connection;
    }
}