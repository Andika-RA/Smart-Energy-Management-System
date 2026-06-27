# Smart City Integrated Platform (Smart Energy & Power Grid)
### Tugas Besar Ujian Akhir Semester — Rekayasa Perangkat Lunak 2 (SE.2)

Platform terintegrasi ini dirancang khusus untuk sub-tema **Smart Energy dan Power Grid**. Sistem ini dibangun dengan arsitektur microservices yang saling berkomunikasi secara aman melalui API Gateway, diamankan dengan protokol OAuth 2.0, serta memanfaatkan layanan Machine Learning untuk mendeteksi anomali jaringan dan memprediksi konsumsi daya secara real-time.

---

## 👥 Informasi Kelompok & Akademik

* **Mata Kuliah**: SE.2 (A)
* **Dosen Pengampu**: Muhammad Panji Muslim, S.Pd., M.Kom.
* **Program Studi**: S1 Informatika
* **Kelompok**: 5

### Anggota Kelompok & Pembagian Kerja

| Nama Anggota | Peran / Tanggung Jawab | Deskripsi Pekerjaan |
| :--- | :--- | :--- |
| **Rafly Dzakki Pratama** | **Ketua Kelompok & ML/IoT Engineer** | Machine Learning Service (FastAPI), IoT Event Ingestion Pipeline (Simulasi Wokwi ESP32, Node-RED, RabbitMQ, Mosquitto MQTT), manifest Kubernetes (K8s), dan Dasbor Monitoring (Prometheus & Grafana). |
| **Hesham Alshami** | **Grid Service Developer** | Pengembangan Grid Service (PHP MVC) untuk mengelola data trafo, wilayah (zone), dan gangguan/insiden jaringan listrik. |
| **Arkhandika Budi Widodoputra** | **Citizen Service Developer** | Pengembangan Citizen Service (PHP MVC) untuk mengelola data warga, pelaporan isu, dan notifikasi warga. |
| **Amanda Puspitarina** | **Power Service Developer** | Pengembangan Power Service (PHP MVC) untuk mengelola pencatatan konsumsi daya, prakiraan energi, dan logs cuaca. |
| **Andika Rafa Akbar** | **Gateway & Auth Engineer** | API Gateway (Node.js/Express), OAuth 2.0 Authorization Server, persiapan repositori utama, dan penyusunan struktur direktori awal. |

---

## 🗺️ Struktur Folder Proyek

```plaintext
├── database/              # Skema SQL, seeder data, dan file Postman Collection API
│   ├── schema.sql         # Definisi skema tabel database
│   ├── seed.sql           # Data awal/dummy (warga, pembacaan sensor, klien OAuth)
│   └── smartcity_platform.postman_collection.json # Ekspor koleksi API Postman
├── express-gateway/       # API Gateway berbasis Node.js dan Express
├── iot/                   # Konfigurasi simulasi IoT
│   ├── simulator.py       # Simulator sensor listrik ESP32
│   ├── mosquitto.conf     # Konfigurasi MQTT Broker Mosquitto
│   └── node-red-data/     # Alur pemrosesan data sensor di Node-RED
├── k8s/                   # Manifest orkestrasi Kubernetes (K8s)
├── monitoring/            # Dasbor pemantauan Prometheus dan Grafana
├── oauth-server/          # Server Otorisasi OAuth 2.0
├── php-citizen/           # Microservice PHP untuk Warga (Citizen) & Pengaduan
├── php-grid/              # Microservice PHP untuk Pengelolaan Jaringan Listrik
├── php-power/             # Microservice PHP untuk Pencatatan Daya & Cuaca
├── python-ml-service/     # FastAPI Service untuk model ML (Prediksi & Anomali)
├── docker-compose.yml     # File orkestrasi Docker Compose utama
└── README.md              # Dokumentasi proyek ini (file ini)
```

---

## ⚙️ Prasyarat Sistem

Sebelum menjalankan platform ini, pastikan perangkat Anda telah terpasang:
* **Docker** (v20.10+) dan **Docker Compose** (v2.0+)
* **Git**
* **Node.js** (v18+) & **npm** (jika ingin menjalankan Gateway/OAuth secara manual)
* **PHP** (v8.1+) & **Composer** (jika ingin menjalankan PHP Service secara manual)
* **Python** (v3.9+) & **pip** (jika ingin menjalankan ML Service secara manual)

---

## 🚀 Langkah Instalasi & Menjalankan Sistem

### 1. Konfigurasi Environment (Keamanan Kredensial)
Untuk menjaga keamanan, **jangan pernah menyimpan password, JWT secret, atau kredensial sensitif di dalam kode yang di-push ke Git**. Proyek ini menggunakan file `.env` lokal untuk menyimpan semua kredensial sensitif tersebut. File `.env` telah didaftarkan di dalam `.gitignore` sehingga aman dan tidak akan terunggah ke repositori GitHub.

Salin file template `.env.example` di root menjadi `.env` lokal Anda:
```bash
cp .env.example .env
```
Setelah disalin, buka file `.env` tersebut dan isi variabel konfigurasi (seperti JWT secret, password DB, dan koneksi RabbitMQ) sesuai dengan lingkungan lokal Anda.

### 2. Menjalankan Seluruh Layanan dengan Docker Compose
Untuk membangun image dan menjalankan seluruh 12 container (MySQL, RabbitMQ, Mosquitto, API Gateway, OAuth Server, Downstream PHP Services, Python ML Service, Prometheus, Grafana, dan IoT Simulator) di latar belakang:
```bash
# Menjalankan container
docker compose up -d --build
```

Untuk melihat logs dari seluruh container yang berjalan:
```bash
docker compose logs -f
```

Untuk menghentikan seluruh layanan:
```bash
docker compose down
```

---

## 🗄️ Inisialisasi Database (Seeding)

Database akan terisi secara otomatis saat inisialisasi Docker Compose pertama kali dijalankan. Jika Anda ingin melakukan reset database dan memasukkan ulang data dummy (seeder) secara manual, jalankan perintah berikut:
```bash
docker exec -i smartcity-mysql mysql -u root -prootpass smartcity < database/seed.sql
```
*(Sesuaikan `rootpass` dengan nilai `DB_PASS` pada file `.env` Anda jika diubah)*.

---

## 🧪 Skenario Demo Utama (Uji Coba)

Sistem ini mendukung pengujian alur kerja end-to-end (E2E) melalui API Gateway pada port `3060`:

### Skenario 1: Uji Status Kesehatan Layanan (Health Checks)
Kirim request `GET` untuk memastikan konektivitas database dari masing-masing microservice:
* API Gateway Health: `GET http://localhost:3060/health`
* Citizen Service: `GET http://localhost:8000/health`
* Power Service: `GET http://localhost:8002/health`
* Grid Service: `GET http://localhost:8001/health`
* ML Service: `GET http://localhost:5000/health`

### Skenario 2: Login Warga & Pembuatan Laporan
1. Dapatkan token JWT menggunakan *Password Grant* warga (password default seeder adalah `secret`):
   ```http
   POST http://localhost:3060/oauth/token
   Content-Type: application/json

   {
     "grant_type": "password",
     "username": "budi@warga.com",
     "password": "secret"
   }
   ```
2. Gunakan `access_token` yang diperoleh untuk mengirim laporan masalah listrik baru melalui Gateway:
   ```http
   POST http://localhost:3060/api/reports
   Authorization: Bearer <Access_Token_Anda>
   Content-Type: application/json

   {
     "title": "Listrik Padam di Blok C",
     "description": "Terjadi pemadaman listrik secara tiba-tiba sejak 10 menit lalu.",
     "category": "outage",
     "zone_id": 2
   }
   ```

### Skenario 3: Pembatasan Akses Khusus Admin (RBAC)
Mencoba memperbarui status laporan menggunakan akun warga biasa akan ditolak oleh API Gateway:
* **Request (Warga Biasa - Ditolak)**:
  ```http
  PATCH http://localhost:3060/api/reports/1/status
  Authorization: Bearer <Token_Warga>
  Content-Type: application/json

  { "status": "resolved" }
  ```
  *Response*: `403 Forbidden - Akses khusus Admin`

* **Request (Admin - Berhasil)**:
  Login sebagai admin (`admin1@smartcity.com` / `secret`) untuk mendapatkan token admin, lalu kirim kembali request di atas. Status laporan akan berhasil diperbarui dengan response `200 OK`.

### Skenario 4: Prediksi ML Real-time & Batch
* **Prediksi Konsumsi Daya (Power Demand)**:
  ```http
  POST http://localhost:3060/predict/power
  Authorization: Bearer <Access_Token>
  Content-Type: application/json

  {
    "id": "req-001",
    "timestamp": "2026-06-28T12:00:00Z",
    "hour": 12,
    "day_of_week": 5,
    "temperature": 31.5,
    "prev_demand": 280.0,
    "zone": "Kawasan Industri A"
  }
  ```

* **Deteksi Anomali Sensor**:
  ```http
  POST http://localhost:3060/detect/anomaly
  Authorization: Bearer <Access_Token>
  Content-Type: application/json

  {
    "id": "req-002",
    "timestamp": "2026-06-28T12:00:00Z",
    "zone": "Pusat Bisnis CBD",
    "sensor_value": 450.0,
    "timestamp_hour": 12,
    "rolling_mean_1h": 220.0,
    "z_score": 3.1
  }
  ```

---

*Seluruh endpoint uji coba lengkap dengan contoh body request dapat langsung diuji secara instan dengan meng-import koleksi Postman yang terletak pada `database/smartcity_platform.postman_collection.json` ke aplikasi Postman Anda.*
