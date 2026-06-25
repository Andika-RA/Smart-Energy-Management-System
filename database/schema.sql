CREATE DATABASE IF NOT EXISTS smartcity;

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



CREATE TABLE IF NOT EXISTS shared_oauth_clients (

    id INT AUTO_INCREMENT PRIMARY KEY,

    client_id VARCHAR(100) UNIQUE NOT NULL,

    client_secret VARCHAR(255) NOT NULL, -- Fix 7: Wajib di-hash menggunakan bcrypt/argon2 di level aplikasi

    grant_types VARCHAR(100) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



CREATE TABLE IF NOT EXISTS shared_oauth_tokens (

    id INT AUTO_INCREMENT PRIMARY KEY,

    client_id VARCHAR(100) NOT NULL,

    user_id INT NULL,

    user_type ENUM('citizen', 'admin', 'service') DEFAULT 'citizen', -- Fix 6: Penanda jenis entitas

    access_token VARCHAR(255) UNIQUE NOT NULL,

    expires_at DATETIME NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);



-- ==========================================

-- CITIZEN SERVICE TABLES

-- ==========================================

CREATE TABLE IF NOT EXISTS citizen_citizens (

    id INT AUTO_INCREMENT PRIMARY KEY,

    nik VARCHAR(16) UNIQUE NOT NULL,

    name VARCHAR(100) NOT NULL,

    email VARCHAR(100) UNIQUE NOT NULL,

    phone VARCHAR(20),

    zone_id INT NOT NULL, -- Fix 4: Ini adalah zona TEMPAT TINGGAL warga

    role ENUM('resident', 'admin') DEFAULT 'resident',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (zone_id) REFERENCES shared_zones(id)

);



-- Foreign Key untuk OAuth tokens ditambahkan setelah tabel citizen ada

ALTER TABLE shared_oauth_tokens 

ADD CONSTRAINT fk_oauth_user 

FOREIGN KEY (user_id) REFERENCES citizen_citizens(id) ON DELETE CASCADE;



CREATE TABLE IF NOT EXISTS citizen_reports (

    id INT AUTO_INCREMENT PRIMARY KEY,

    citizen_id INT NOT NULL,

    category VARCHAR(50) NOT NULL,

    description TEXT NOT NULL,

    zone_id INT NOT NULL, -- Fix 4: Ini adalah LOKASI KEJADIAN keluhan

    status ENUM('pending', 'investigating', 'resolved') DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (citizen_id) REFERENCES citizen_citizens(id),

    FOREIGN KEY (zone_id) REFERENCES shared_zones(id)

);



CREATE TABLE IF NOT EXISTS citizen_notifications (

    id INT AUTO_INCREMENT PRIMARY KEY,

    citizen_id INT NULL, -- Fix 1: Bisa di-NULL-kan jika pesannya untuk semua orang

    is_broadcast BOOLEAN DEFAULT FALSE, -- Fix 1: Penanda bahwa pesan ini untuk seluruh kota

    title VARCHAR(100) NOT NULL,

    body TEXT NOT NULL,

    is_read BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (citizen_id) REFERENCES citizen_citizens(id) ON DELETE CASCADE

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

-- POWER SERVICE TABLES (Konsumsi & Lingkungan)

-- ==========================================

CREATE TABLE IF NOT EXISTS power_demands (

    id INT AUTO_INCREMENT PRIMARY KEY,

    zone_id INT NOT NULL,

    power_demand_kw FLOAT NOT NULL,

    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (zone_id) REFERENCES shared_zones(id)

);



CREATE TABLE IF NOT EXISTS power_weather_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    zone_id INT NOT NULL,

    temperature FLOAT NOT NULL,

    humidity FLOAT NOT NULL,

    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (zone_id) REFERENCES shared_zones(id)

);



CREATE TABLE IF NOT EXISTS power_forecasts (

    id INT AUTO_INCREMENT PRIMARY KEY,

    zone_id INT NOT NULL,

    predicted_demand_kw FLOAT NOT NULL,

    status_level ENUM('Lancar', 'Sedang', 'Padat') NOT NULL,

    forecast_for_time TIMESTAMP NOT NULL,

    model_version VARCHAR(50) NOT NULL DEFAULT 'RandomForest_v1.0', -- Fix 5: Kemampuan audit model

    generated_from TIMESTAMP NULL, -- Fix 5: Acuan data mentah

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (zone_id) REFERENCES shared_zones(id),

    UNIQUE KEY unique_forecast (zone_id, forecast_for_time) -- Fix 9: Mencegah record duplikat dari ML untuk jam yang sama

);



-- ==========================================

-- INDEXING UNTUK PERFORMA

-- ==========================================

CREATE INDEX idx_citizen_zone ON citizen_citizens(zone_id);

CREATE INDEX idx_grid_recorded ON grid_readings(recorded_at);

CREATE INDEX idx_power_recorded ON power_demands(recorded_at);

CREATE INDEX idx_forecast_time ON power_forecasts(forecast_for_time);