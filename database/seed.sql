USE smartcity;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE shared_oauth_tokens;
TRUNCATE TABLE power_forecasts;
TRUNCATE TABLE power_weather_logs;
TRUNCATE TABLE power_demands;
TRUNCATE TABLE grid_incidents;
TRUNCATE TABLE grid_readings;
TRUNCATE TABLE citizen_notifications;
TRUNCATE TABLE citizen_reports;
TRUNCATE TABLE citizen_citizens;
TRUNCATE TABLE shared_oauth_clients;
TRUNCATE TABLE shared_zones;

INSERT INTO shared_zones (name, city_district, coordinates, max_capacity_ampere, transformer_capacity_kva, nominal_voltage, area_km2, health_status) VALUES
('Kawasan Industri A', 'Distrik Utara', '-6.200000, 106.816666', 1000.0, 500.0, 220.0, 15.5, 'normal'),
('Perumahan Griya', 'Distrik Selatan', '-6.210000, 106.826666', 200.0, 100.0, 220.0, 5.2, 'normal'),
('Pusat Bisnis CBD', 'Distrik Pusat', '-6.220000, 106.836666', 800.0, 400.0, 220.0, 8.0, 'warning'),
('Kawasan Pendidikan', 'Distrik Timur', '-6.230000, 106.846666', 300.0, 150.0, 220.0, 10.1, 'normal'),
('Pasar Tradisional', 'Distrik Barat', '-6.240000, 106.856666', 250.0, 125.0, 220.0, 4.5, 'critical');

INSERT INTO shared_oauth_clients (client_id, client_secret, grant_types, redirect_uris) VALUES
('mobile_app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password,refresh_token', 'smartcity://callback'),
('web_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password,refresh_token', 'https://admin.smartcity.local/callback'),
('iot_gateway', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client_credentials', NULL);

INSERT INTO citizen_citizens (nik, name, email, password, phone, zone_id, role) VALUES
('3171234567890001', 'Rafly Dzakki', 'admin@smartcity.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08111222333', 1, 'admin'),
('3171234567890002', 'Budi Santoso', 'budi@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567002', 2, 'resident'),
('3171234567890003', 'Siti Aminah', 'siti@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567003', 3, 'resident'),
('3171234567890004', 'Agus Setiawan', 'agus@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567004', 4, 'resident'),
('3171234567890005', 'Dewi Lestari', 'dewi@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567005', 5, 'resident'),
('3171234567890006', 'Andi Pratama', 'andi@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567006', 1, 'resident'),
('3171234567890007', 'Rina Wati', 'rina@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567007', 2, 'resident'),
('3171234567890008', 'Hendra Wijaya', 'hendra@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567008', 3, 'resident'),
('3171234567890009', 'Lina Marlina', 'lina@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567009', 4, 'resident'),
('3171234567890010', 'Dedi Syahputra', 'dedi@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567010', 5, 'resident'),
('3171234567890011', 'Ayu Ningsih', 'ayu@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567011', 1, 'resident'),
('3171234567890012', 'Rizky Maulana', 'rizky@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567012', 2, 'resident'),
('3171234567890013', 'Maya Indah', 'maya@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567013', 3, 'resident'),
('3171234567890014', 'Iwan Fals', 'iwan@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567014', 4, 'resident'),
('3171234567890015', 'Nia Ramadhani', 'nia@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567015', 5, 'resident'),
('3171234567890016', 'Tono Harjono', 'tono@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567016', 1, 'resident'),
('3171234567890017', 'Fitri Carlina', 'fitri@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567017', 2, 'resident'),
('3171234567890018', 'Joko Susilo', 'joko@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567018', 3, 'resident'),
('3171234567890019', 'Yuni Shara', 'yuni@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567019', 4, 'resident'),
('3171234567890020', 'Eko Patrio', 'eko@warga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567020', 5, 'resident');

INSERT INTO citizen_reports (citizen_id, category, description, zone_id, status) VALUES
(2, 'Kelistrikan', 'Kabel listrik di depan rumah putus akibat tertimpa pohon.', 2, 'investigating'),
(3, 'Fasilitas', 'Lampu jalan mati total sejak dua hari lalu.', 3, 'pending'),
(4, 'Kelistrikan', 'Gardu listrik mengeluarkan suara dengung keras.', 4, 'resolved'),
(5, 'Lalu Lintas', 'Lampu merah di persimpangan pasar mati.', 5, 'investigating'),
(6, 'Lingkungan', 'Ada pembuangan limbah ilegal di sungai.', 1, 'pending'),
(7, 'Kelistrikan', 'Sering terjadi tegangan turun saat malam hari.', 2, 'resolved'),
(8, 'Jalan', 'Jalan berlubang cukup dalam, membahayakan pengendara.', 3, 'pending'),
(9, 'Kelistrikan', 'Tiang listrik miring hampir roboh.', 4, 'investigating'),
(10, 'Fasilitas', 'Pompa air publik tidak menyala.', 5, 'pending'),
(11, 'Kelistrikan', 'Pemadaman listrik tanpa pemberitahuan.', 1, 'resolved'),
(12, 'Lingkungan', 'Tumpukan sampah belum diangkut selama 3 hari.', 2, 'pending'),
(13, 'Lalu Lintas', 'Sensor parkir pintar rusak.', 3, 'investigating'),
(14, 'Kelistrikan', 'Meteran listrik mengeluarkan percikan api.', 4, 'pending'),
(15, 'Fasilitas', 'CCTV jalan rusak tidak merekam.', 5, 'resolved'),
(16, 'Jalan', 'Banjir menutupi akses jalan utama.', 1, 'investigating'),
(17, 'Kelistrikan', 'Ada kabel menjuntai rendah ke aspal.', 2, 'pending'),
(18, 'Lingkungan', 'Asap hitam pekat dari pabrik terdekat.', 3, 'resolved'),
(19, 'Kelistrikan', 'Tegangan listrik naik turun merusak TV.', 4, 'investigating'),
(20, 'Fasilitas', 'Halte bus pintar layarnya mati.', 5, 'pending'),
(2, 'Kelistrikan', 'Bunyi ledakan kecil dari trafo tiang.', 2, 'pending');

INSERT INTO citizen_notifications (citizen_id, is_broadcast, title, body, is_read) VALUES
(NULL, 1, 'Pemeliharaan Sistem', 'Akan ada pemadaman listrik bergilir besok dari jam 09.00 - 12.00 di seluruh kota.', 0),
(NULL, 1, 'Kualitas Udara Buruk', 'Warga diimbau menggunakan masker karena AQI masuk kategori Unhealthy.', 0),
(2, 0, 'Laporan Diproses', 'Laporan Anda terkait kabel putus sedang diinvestigasi oleh teknisi kami.', 1),
(4, 0, 'Laporan Selesai', 'Terima kasih, laporan gardu listrik Anda sudah diselesaikan.', 0),
(NULL, 1, 'Peringatan Banjir', 'Debit air sungai meningkat. Harap waspada untuk warga bantaran.', 0),
(3, 0, 'Laporan Diterima', 'Laporan jalan berlubang Anda masuk antrean perbaikan.', 0),
(6, 0, 'Peringatan Tegangan', 'Tegangan di zona Anda saat ini tidak stabil. Harap cabut alat elektronik sensitif.', 1),
(7, 0, 'Laporan Diproses', 'Laporan tegangan turun sedang ditindaklanjuti.', 0),
(NULL, 1, 'Bayar Tagihan', 'Jangan lupa membayar tagihan listrik tepat waktu untuk menghindari pemutusan.', 1),
(10, 0, 'Pemberitahuan Sistem', 'Akun Anda berhasil diverifikasi.', 1),
(14, 0, 'Awas Korsleting', 'Sistem mendeteksi potensi korsleting dari laporan Anda.', 0),
(15, 0, 'Laporan Selesai', 'CCTV jalan sudah diperbaiki.', 1),
(1, 0, 'Admin Alert', 'Terdapat 5 laporan baru terkait kelistrikan hari ini.', 0),
(1, 0, 'System Health', 'Node-RED gateway terpantau sehat.', 1),
(NULL, 1, 'Cuaca Ekstrem', 'Hujan badai diprediksi akan turun nanti malam.', 0),
(8, 0, 'Laporan Diterima', 'Keluhan jalan Anda sudah kami teruskan ke Dinas PU.', 1),
(9, 0, 'Peringatan Bahaya', 'Mohon jauhi tiang listrik yang miring.', 0),
(11, 0, 'Laporan Selesai', 'Pemadaman listrik sudah ditangani.', 1),
(18, 0, 'Laporan Selesai', 'Investigasi asap pabrik selesai dilakukan.', 0),
(20, 0, 'Laporan Diterima', 'Keluhan halte bus masuk ke sistem antrean.', 0);

INSERT INTO grid_readings (zone_id, voltage, current, power_factor) VALUES
(1, 220.5, 350.2, 0.95),
(2, 218.4, 45.5, 0.88),
(3, 219.0, 210.1, 0.92),
(4, 221.2, 110.4, 0.90),
(5, 205.5, 240.8, 0.82),
(1, 221.0, 360.5, 0.96),
(2, 219.5, 48.2, 0.89),
(3, 220.1, 215.3, 0.93),
(4, 222.0, 112.5, 0.91),
(5, 201.0, 245.0, 0.80),
(1, 222.5, 355.0, 0.95),
(2, 220.0, 50.1, 0.90),
(3, 218.5, 208.5, 0.92),
(4, 221.8, 109.0, 0.90),
(5, 198.5, 248.5, 0.78),
(1, 219.8, 358.2, 0.96),
(2, 220.5, 46.8, 0.89),
(3, 221.0, 212.0, 0.93),
(4, 222.5, 115.0, 0.91),
(5, 195.0, 250.0, 0.75);

INSERT INTO grid_incidents (zone_id, type, severity, status, description) VALUES
(5, 'Voltage Drop', 'critical', 'open', 'Tegangan turun drastis di bawah 200V secara konstan.'),
(1, 'Overload', 'high', 'investigating', 'Arus beban industri hampir menyentuh batas kapasitas trafo.'),
(2, 'Short Circuit', 'critical', 'resolved', 'Terjadi korsleting di gardu distribusi perumahan.'),
(3, 'Power Spike', 'medium', 'open', 'Lonjakan daya tiba-tiba terdeteksi oleh sensor.'),
(4, 'Phase Loss', 'high', 'investigating', 'Satu fase listrik hilang pada jaringan utama.'),
(5, 'Voltage Drop', 'high', 'resolved', 'Masalah pada kabel netral gardu.'),
(1, 'Overload', 'medium', 'open', 'Pabrik tekstil menggunakan daya melebihi kontrak.'),
(2, 'Brownout', 'low', 'investigating', 'Peredupan lampu dilaporkan oleh beberapa warga.'),
(3, 'Harmonics', 'medium', 'resolved', 'Distorsi harmonik tinggi dari pusat perbelanjaan.'),
(4, 'Equipment Failure', 'critical', 'open', 'Recloser trafo tidak merespons.'),
(5, 'Voltage Drop', 'critical', 'investigating', 'Tegangan sisa 195V.'),
(1, 'Overload', 'high', 'resolved', 'Beban diturunkan paksa dari pusat.'),
(2, 'Short Circuit', 'critical', 'open', 'Kabel tanah terputus ekskavator.'),
(3, 'Power Spike', 'medium', 'investigating', 'Lonjakan akibat petir.'),
(4, 'Phase Loss', 'high', 'resolved', 'Perbaikan sekring putus selesai.'),
(5, 'Equipment Failure', 'high', 'open', 'Suhu trafo melebihi batas aman (80 derajat).'),
(1, 'Harmonics', 'low', 'investigating', 'Pemantauan kualitas daya harian.'),
(2, 'Brownout', 'medium', 'resolved', 'Gardu telah diseimbangkan.'),
(3, 'Overload', 'high', 'open', 'Penggunaan AC serentak saat suhu 36 derajat.'),
(4, 'Short Circuit', 'critical', 'investigating', 'Terdeteksi arus 500A sesaat sebelum trip.');

INSERT INTO power_demands (zone_id, power_demand_kw) VALUES
(1, 73.5), (2, 8.5), (3, 44.5), (4, 22.0), (5, 40.0),
(1, 75.2), (2, 9.1), (3, 46.0), (4, 23.5), (5, 42.1),
(1, 78.0), (2, 10.0), (3, 48.2), (4, 25.0), (5, 45.5),
(1, 76.5), (2, 9.5), (3, 47.0), (4, 24.0), (5, 43.0);

INSERT INTO power_weather_logs (zone_id, temperature, humidity) VALUES
(1, 32.5, 65.0), (2, 31.0, 70.0), (3, 33.0, 60.0), (4, 30.5, 75.0), (5, 34.0, 55.0),
(1, 33.0, 63.0), (2, 31.5, 68.0), (3, 33.5, 58.0), (4, 31.0, 73.0), (5, 34.5, 53.0),
(1, 34.0, 60.0), (2, 32.5, 65.0), (3, 35.0, 55.0), (4, 32.0, 70.0), (5, 36.0, 50.0),
(1, 33.5, 62.0), (2, 32.0, 67.0), (3, 34.2, 57.0), (4, 31.5, 72.0), (5, 35.5, 52.0);

INSERT INTO power_forecasts (zone_id, predicted_demand_kw, status_level, forecast_for_time, model_version) VALUES
(1, 80.5, 'Padat', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'RandomForest_v1.0'),
(2, 12.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'RandomForest_v1.0'),
(3, 55.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'RandomForest_v1.0'),
(4, 28.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'RandomForest_v1.0'),
(5, 50.0, 'Padat', DATE_ADD(NOW(), INTERVAL 1 HOUR), 'RandomForest_v1.0'),

(1, 85.0, 'Padat', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'RandomForest_v1.0'),
(2, 15.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'RandomForest_v1.0'),
(3, 60.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'RandomForest_v1.0'),
(4, 30.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'RandomForest_v1.0'),
(5, 55.0, 'Padat', DATE_ADD(NOW(), INTERVAL 2 HOUR), 'RandomForest_v1.0'),

(1, 75.0, 'Padat', DATE_ADD(NOW(), INTERVAL 3 HOUR), 'RandomForest_v1.0'),
(2, 10.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 3 HOUR), 'RandomForest_v1.0'),
(3, 45.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 3 HOUR), 'RandomForest_v1.0'),
(4, 25.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 3 HOUR), 'RandomForest_v1.0'),
(5, 45.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 3 HOUR), 'RandomForest_v1.0'),

(1, 70.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'RandomForest_v1.0'),
(2, 8.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'RandomForest_v1.0'),
(3, 40.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'RandomForest_v1.0'),
(4, 20.0, 'Lancar', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'RandomForest_v1.0'),
(5, 40.0, 'Sedang', DATE_ADD(NOW(), INTERVAL 4 HOUR), 'RandomForest_v1.0');

SET FOREIGN_KEY_CHECKS = 1;