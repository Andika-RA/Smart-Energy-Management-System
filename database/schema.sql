CREATE DATABASE IF NOT EXISTS smartcity;
USE smartcity;

CREATE TABLE IF NOT EXISTS citizen_citizens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(16) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    zone_id INT,
    role VARCHAR(20) DEFAULT 'warga',
    created_at DATETIME
);
