CREATE TABLE IF NOT EXISTS grid_quality (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voltage FLOAT,
    current FLOAT,
    power_factor FLOAT,
    temperature FLOAT,
    humidity FLOAT,
    zone VARCHAR(50),
    grid_status VARCHAR(50),
    timestamp DATETIME
);
