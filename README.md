# Tugas Besar UAS - Rekayasa Perangkat Lunak 2 (SE.2)
## Sistem Smart Energy & Power Grid - Kelompok 5

Repository ini dibuat untuk memenuhi tugas besar UAS mata kuliah Rekayasa Perangkat Lunak 2 (SE.2) kelas A, S1 Informatika. Sistem yang dibangun adalah platform manajemen energi pintar (Smart Energy & Power Grid) berbasis microservices. 

Sistem ini terintegrasi menggunakan API Gateway, OAuth 2.0 Authorization Server, PHP MVC Service (Citizen, Power, Grid), Python ML Service (FastAPI) untuk prediksi beban daya & anomali, serta pipeline IoT berbasis MQTT dan Node-RED.

---

## Detail Kelompok 5

* **Mata Kuliah**: Rekayasa Perangkat Lunak 2 (SE.2 - A)
* **Dosen Pengampu**: Muhammad Panji Muslim, S.Pd., M.Kom.
* **Program Studi**: S1 Informatika

### Anggota Kelompok & Pembagian Tugas

| Nama Anggota | Peran & Tanggung Jawab | Deskripsi Pekerjaan |
| :--- | :--- | :--- |
| **Rafly Dzakki Pratama** | **Ketua Kelompok & ML/IoT Engineer** | Membuat Machine Learning Service (FastAPI), IoT Event Ingestion Pipeline (Simulasi Wokwi ESP32, Node-RED, RabbitMQ, Mosquitto MQTT), manifest Kubernetes (K8s), dan dashboard monitoring (Prometheus & Grafana). |
| **Hesham Alshami** | **Grid Service Developer** | Membuat Grid Service (PHP MVC) untuk mengelola data trafo, wilayah (zone), dan catatan gangguan/insiden listrik. |
| **Arkhandika Budi Widodoputra** | **Citizen Service Developer** | Membuat Citizen Service (PHP MVC) untuk mengelola data warga, input laporan/pengaduan, dan notifikasi. |
| **Amanda Puspitarina** | **Power Service Developer** | Membuat Power Service (PHP MVC) untuk mengelola pencatatan konsumsi daya, prakiraan energi (forecast), dan log cuaca. |
| **Andika Rafa Akbar** | **Gateway & Auth Engineer** | Membuat API Gateway (Node.js/Express) dengan rate-limiting & RBAC, OAuth 2.0 Authorization Server, perancangan desain arsitektur, pembuatan Postman collection, serta setup awal repository & struktur direktori. |

---

## Struktur Folder

```plaintext
├── database/              # Skema SQL, seeder data, dan file Postman Collection API
│   ├── schema.sql         # Skema tabel database MySQL
│   ├── seed.sql           # Data dummy awal (warga, pembacaan sensor, client OAuth)
│   └── smartcity_platform.postman_collection.json # File JSON Postman Collection
├── express-gateway/       # API Gateway (Node.js & Express)
├── iot/                   # File simulasi IoT
│   ├── simulator.py       # Simulator sensor listrik ESP32
│   ├── mosquitto.conf     # Konfigurasi MQTT Broker Mosquitto
│   └── node-red-data/     # Alur flow Node-RED
├── k8s/                   # Manifest deploy ke Kubernetes
├── monitoring/            # Setup dashboard Prometheus & Grafana
├── oauth-server/          # Server Otorisasi OAuth 2.0
├── php-citizen/           # Service Warga (PHP MVC)
├── php-grid/              # Service Jaringan Listrik (PHP MVC)
├── php-power/             # Service Pencatatan Daya (PHP MVC)
├── python-ml-service/     # Layanan ML (FastAPI) untuk prediksi & anomali
├── docker-compose.yml     # Konfigurasi Docker Compose utama
└── README.md              # File dokumentasi proyek (file ini)
```

---

## Prasyarat (Prerequisites)

### Opsi 1: Dijalankan Menggunakan Docker Compose (Direkomendasikan):
* **Docker & Docker Compose**
* **Git**

### Opsi 2: Dijalankan Menggunakan Kubernetes:
* **kubectl** (tool CLI untuk interaksi dengan kluster Kubernetes)
* **Minikube** atau **kind** (untuk menjalankan kluster Kubernetes lokal)

### Opsi 3: Dijalankan Secara Manual (Tanpa Kontainer):
* **MySQL** (v8.0+, untuk penyimpanan skema database)
* **RabbitMQ** (sebagai event broker pengiriman pesan antar-layanan)
* **Mosquitto** (sebagai MQTT broker penangkap data sensor IoT)
* **Node-RED** (untuk alur pengolahan dan visualisasi data sensor IoT)
* **Node.js** (v18+) & **npm** (untuk menjalankan API Gateway & OAuth Server)
* **PHP** (v8.1+) & **Composer** (untuk menjalankan Citizen, Power, & Grid Service)
* **Python** (v3.9+) & **pip** (untuk menjalankan Python ML Service)

---

## Cara Menjalankan Sistem

### 1. Setup File .env
Bikin file `.env` di root folder dengan cara copy dari `.env.example`. Sesuaikan isi password DB, JWT secret, dan RabbitMQ dengan lingkungan (environment) pengembangan Anda. File `.env` ini sudah masuk `.gitignore` jadi aman dan tidak akan terunggah ke Git.
```bash
cp .env.example .env
```

### 2. Jalankan lewat Docker Compose
Untuk men-build dan menyalakan semua container di background, ketik:
```bash
docker compose up -d --build
```

Untuk melihat logs dari service yang berjalan:
```bash
docker compose logs -f
```

Untuk mematikan sistem:
```bash
docker compose down
```

---

## Setup Database (Seed Data)

Saat docker-compose pertama kali dijalankan, database MySQL bakal otomatis terbuat dan terisi data dummy (seed). Jika ingin melakukan reset database dan mengisi data seed secara manual, jalankan perintah ini:
```bash
docker exec -i smartcity-mysql mysql -u <DB_USER> -p<DB_PASS> <DB_NAME> < database/seed.sql
```
*(Ganti `<DB_USER>`, `<DB_PASS>`, dan `<DB_NAME>` sesuai konfigurasi file `.env`)*.

---

## Endpoint Utama (lewat Gateway)

Berikut adalah daftar endpoint utama yang diekspos melalui API Gateway:

| Method | Path | Service |
| :--- | :--- | :--- |
| `POST` | `/oauth/token`, `/oauth/introspect`, `/oauth/revoke` | `oauth-server` |
| `GET/POST` | `/api/citizens` | `php-citizen` |
| `GET/POST/PATCH` | `/api/reports`, `/api/reports/:id/status` | `php-citizen` |
| `GET` | `/api/notifications` | `php-citizen` |
| `GET/POST/PUT/DELETE` | `/api/zones`, `/api/grid-readings`, `/api/grid-incidents` | `php-grid` |
| `GET/POST/PUT/DELETE` | `/api/power`, `/api/weather`, `/api/forecast` | `php-power` |
| `POST` | `/predict/power`, `/predict/grid-quality`, `/detect/anomaly` | `python-ml-service` |
| `GET` | `/health` | seluruh service |

---

## Pengujian & Demonstrasi Sistem

Seluruh pengujian fungsionalitas sistem (skenario E2E mulai dari ingesti data IoT, autentikasi OAuth 2.0 dengan Bcrypt, verifikasi JWT lokal di Gateway, validasi role RBAC, inferensi ML real-time, hingga pengiriman notifikasi anomali secara asinkron) dapat langsung dicoba menggunakan file koleksi API Postman:
* **`database/smartcity_platform.postman_collection.json`**

Silakan import file koleksi tersebut ke dalam aplikasi Postman untuk memulai simulasi pengujian lengkap.

