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

CREATE TABLE IF NOT EXISTS citizen_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    zone_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME
);

CREATE TABLE IF NOT EXISTS citizen_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME
);