USE smartcity;

-- Masukkan 5 Zona Dummy
INSERT INTO shared_zones (name, city_district, max_capacity_ampere, transformer_capacity_kva, nominal_voltage, area_km2) VALUES 
('Zona 1 Residensial', 'Distrik Pusat', 300, 150, 220, 5.5),
('Zona 2 Industri', 'Distrik Utara', 800, 500, 220, 12.0),
('Zona 3 Komersial', 'Distrik Selatan', 500, 300, 220, 8.2),
('Zona 4 Pendidikan', 'Distrik Timur', 400, 200, 220, 6.0),
('Zona 5 Pinggiran', 'Distrik Barat', 200, 100, 220, 15.5);