USE smartcity;

-- Masukkan 5 Zona Dummy
INSERT INTO shared_zones (name, city_district, max_capacity_ampere, transformer_capacity_kva, nominal_voltage, area_km2) VALUES 
('Zona 1 Residensial', 'Distrik Pusat', 300, 150, 220, 5.5),
('Zona 2 Industri', 'Distrik Utara', 800, 500, 220, 12.0),
('Zona 3 Komersial', 'Distrik Selatan', 500, 300, 220, 8.2),
('Zona 4 Pendidikan', 'Distrik Timur', 400, 200, 220, 6.0),
('Zona 5 Pinggiran', 'Distrik Barat', 200, 100, 220, 15.5);

-- Masukkan Dummy OAuth Clients (Secret di-hash menggunakan bcrypt)
INSERT INTO shared_oauth_clients (client_id, client_secret, grant_types) VALUES
('smart-city-client', '$2b$10$himfVZGSNlhIyy84KtegA.GvYK3Q2NaPmgBssK475j0y4lHSZP3Gi', 'client_credentials,password,refresh_token'),
('smart-energy-client', '$2b$10$aTNb478uWbzAljXYuCfahOEFVNcoTewDwQ6e82gE4MDmPYoRQCsJS', 'client_credentials,password,refresh_token');

-- Masukkan Dummy Citizens untuk Password Grant (NIK sebagai password)
INSERT INTO citizen_citizens (nik, name, email, phone, zone_id, role) VALUES
('1234567890123456', 'Warga Biasa', 'warga@smartcity.go.id', '08123456789', 1, 'resident'),
('1111111111111111', 'Admin Kota', 'admin@smartcity.go.id', '08111111111', 1, 'admin');