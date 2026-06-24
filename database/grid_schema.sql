USE smartcity;

-- ==========================================
-- SHARED TABLES (Digunakan lintas service)
-- ==========================================
CREATE TABLE IF NOT EXISTS shared_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    city_district VARCHAR(100) NOT NULL,
    max_capacity_ampere FLOAT NOT NULL,
    transformer_capacity_kva FLOAT NOT NULL DEFAULT 0, -- Fix 11: Kapasitas trafo KVA
    nominal_voltage FLOAT NOT NULL DEFAULT 220, -- Fix 11: Tegangan nominal gardu
    area_km2 FLOAT,
    health_status ENUM('normal', 'warning', 'critical') DEFAULT 'normal', -- Fix 2: Status Gardu
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- GRID SERVICE TABLES (Infrastruktur Kelistrikan)
-- ==========================================
CREATE TABLE IF NOT EXISTS grid_readings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    voltage FLOAT NOT NULL CHECK (voltage >= 0 AND voltage <= 260), -- Fix 8: Validasi nilai realistis
    current FLOAT NOT NULL CHECK (current >= 0 AND current <= 500), -- Fix 8
    power_factor FLOAT NOT NULL CHECK (power_factor >= 0 AND power_factor <= 1), -- Fix 8
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES shared_zones(id)
);

CREATE TABLE IF NOT EXISTS grid_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    status ENUM('open', 'investigating', 'resolved') DEFAULT 'open', -- Fix 10: Status insiden yang spesifik
    description TEXT NOT NULL,
    resolved_at TIMESTAMP NULL,
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES shared_zones(id)
);

-- ==========================================
-- INDEXING UNTUK PERFORMA
-- ==========================================
CREATE INDEX idx_grid_recorded ON grid_readings(recorded_at);
