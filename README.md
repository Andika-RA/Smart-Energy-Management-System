# Smart Energy Management System

Sistem manajemen energi kota pintar berbasis microservices: data sensor IoT
(listrik & cuaca) dialirkan lewat MQTT → Node-RED → API Gateway → service PHP,
dianalisis oleh service Machine Learning Python, dan peringatan anomali
diteruskan ke warga lewat RabbitMQ.

## Arsitektur Layanan

| Service | Tipe | Port (Docker Compose) | Tanggung Jawab |
|---|---|---|---|
| `api-gateway` | Node.js (Express) | 3000 | Satu pintu masuk publik: routing, JWT auth, rate limit |
| `oauth-server` | Node.js (Express) | 3002 | Penerbitan & verifikasi token OAuth2 (JWT) |
| `php-citizen` | PHP | 8000 | Data warga, laporan, notifikasi |
| `php-grid` | PHP | 8001 | Data infrastruktur jaringan listrik (zona, grid reading, insiden) |
| `php-power` | PHP | 8002 | Data konsumsi daya, cuaca, dan hasil forecast |
| `python-ml-service` | Python (FastAPI) | 5000 | Prediksi demand, kualitas grid, deteksi anomali |
| `mysql` | MySQL 8 | 3306 (internal) | Database utama (`smartcity`) |
| `rabbitmq` | RabbitMQ | 5672 / 15672 | Message broker antar service |
| `mosquitto` | MQTT broker | 1883 | Broker MQTT lokal (opsional, lihat catatan IoT) |
| `node-red` | Node-RED | 1880 | Jembatan data sensor (MQTT → Gateway, via HTTP) |
| `prometheus` / `grafana` | Monitoring | 9090 / 3001 | Metrik & dashboard |

## Menjalankan dengan Docker Compose

```bash
cp .env.example .env
# sesuaikan nilai DB_PASS, JWT_SECRET, dll di .env

make up        # = docker compose up -d --build
make logs      # lihat log seluruh service
make down      # matikan semua container
```

Database (`schema.sql` lalu `seed.sql`) otomatis ter-load saat container
`mysql` pertama kali dibuat (lewat `docker-entrypoint-initdb.d`).

## Menjalankan di Kubernetes

```bash
# Build & load image lokal ke cluster (contoh untuk kind/minikube) SEBELUM apply,
# karena seluruh manifest memakai imagePullPolicy: Never:
docker compose build
# kind load docker-image smart-city-platform-<nama-service>:latest   (ulangi per image)

make k8s-deploy   # = kubectl apply -f k8s/
make k8s-status   # = kubectl get all -n smartcity-platform
make k8s-down
```

## Endpoint Utama (lewat Gateway)

| Method | Path | Service |
|---|---|---|
| POST | `/oauth/token`, `/oauth/introspect`, `/oauth/revoke` | oauth-server |
| GET/POST | `/api/citizens` | php-citizen |
| GET/POST/PATCH | `/api/reports`, `/api/reports/:id/status` | php-citizen |
| GET | `/api/notifications` | php-citizen |
| GET/POST/PUT/DELETE | `/api/zones`, `/api/grid-readings`, `/api/grid-incidents` | php-grid |
| GET/POST/PUT/DELETE | `/api/power`, `/api/weather`, `/api/forecast` | php-power |
| POST | `/predict/power`, `/predict/grid-quality`, `/detect/anomaly` | python-ml-service |
| GET | `/health` | seluruh service |