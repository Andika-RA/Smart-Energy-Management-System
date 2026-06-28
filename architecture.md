# Desain Arsitektur Sistem — Smart City Integrated Platform

Dokumen ini menjelaskan rancangan arsitektur, detail komponen, dan aliran data terintegrasi untuk platform Smart City (sub-tema Smart Energy & Power Grid).

---

## Gambaran Arsitektur Keseluruhan

Berikut adalah arsitektur lengkap Smart City Integrated Platform yang diimplementasikan dalam sistem:

| Layer | Komponen | Teknologi | Port Internal | Port Host (Docker Compose) | Port K8s (NodePort) | Fungsi |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **IoT Layer** | Sensor Gateway | Node-RED + Mosquitto MQTT | 1883 / 1880 | 1885 / 1888 | - | Menerima data sensor dari perangkat IoT (grid), lalu menerbitkannya ke event broker. |
| **IoT Layer** | IoT Device Simulator | Python / Wokwi ESP32 | - | - | - | Mensimulasikan sensor tegangan (voltage), arus (current), dan suhu secara real-time. |
| **Gateway Layer** | API Gateway | Express.js + http-proxy-middleware | 3060 | 3065 | 30065 | Routing, verifikasi token JWT lokal, rate limiting, pembatasan hak akses (RBAC), dan agregasi kesehatan sistem. |
| **Gateway Layer** | OAuth Server | Express.js + MySQL | 3002 | 3005 | - | Menerbitkan, memverifikasi, dan mencabut access token / refresh token OAuth 2.0 secara stateful. |
| **Service Layer** | Citizen Service | PHP 8.2 MVC + PDO | 80 / 8000 | 8005 | - | Mengelola CRUD data warga, pelaporan masalah perkotaan, dan pengiriman notifikasi warga. |
| **Service Layer** | Grid Service | PHP 8.2 MVC + PDO | 80 / 8001 | 8015 | - | Mengelola data wilayah (zone), kapasitas trafo, pemantauan voltage/current, dan insiden padam. |
| **Service Layer** | Power Service | PHP 8.2 MVC + PDO | 80 / 8002 | 8025 | - | Mengelola pencatatan konsumsi daya listrik kota, logs cuaca, dan data prakiraan energi (forecast). |
| **ML Layer** | Prediction Service | Python 3.11 + FastAPI | 5000 | 5005 | - | Menyediakan API prediksi konsumsi daya (regresi), klasifikasi kualitas jaringan, dan deteksi anomali. |
| **Messaging** | Message Broker | RabbitMQ 3.12 | 5672 / 15672 | 5675 / 15675 | - (ClusterIP) | Saluran komunikasi asinkron event-driven antar-layanan (microservices). |
| **Monitoring** | Metrics & Dashboard | Prometheus + Grafana | 9090 / 3000 | 9095 / 3015 | - | Pengumpulan metrik performa sistem dan visualisasi dasbor pemantauan. |
| **Infra** | Container Runtime | Docker + Docker Compose | - | - | - | Packaging dan isolasi container untuk semua layanan. |
| **Infra** | Orchestration | Kubernetes (kubectl, k3d) | - | - | - | Penyediaan manifest untuk deployment, autoscaling (HPA), dan pemulihan mandiri (self-healing). |

---

## Diagram Komponen Sistem

```mermaid
graph TD
    Client[Klien / Aplikasi Web] -->|HTTP Requests| Gateway[API Gateway: Port 3060]
    Gateway -->|1. Autentikasi / Introspeksi| OAuth[OAuth 2.0 Server: Port 3002]
    
    subgraph Microservices PHP MVC
        Citizen[Citizen Service: Port 8000]
        Power[Power Service: Port 8002]
        Grid[Grid Service: Port 8001]
    end
    
    Gateway -->|Route: /api/citizens| Citizen
    Gateway -->|Route: /api/power| Power
    Gateway -->|Route: /api/grid| Grid
    
    subgraph Machine Learning Service
        ML[ML Service: Port 5000]
    end
    
    Gateway -->|Route: /predict| ML
    Gateway -->|Route: /detect| ML
    
    subgraph Pipeline IoT & Event Broker
        Wokwi[Simulator Sensor ESP32] -->|MQTT| Mosquitto[MQTT Broker: Port 1883]
        Mosquitto -->|Subscribe| NodeRED[Node-RED Gateway: Port 1880]
        NodeRED -->|Publish AMQP| RabbitMQ[RabbitMQ: Port 5672]
        RabbitMQ -->|Consume anomaly.alert| Citizen
        RabbitMQ -->|Consume grid.new| ML
    end
    
    subgraph Database Penyimpanan
        DB[(Database MySQL)]
    end
    
    Citizen -->|PDO| DB
    Power -->|PDO| DB
    Grid -->|PDO| DB
    OAuth -->|SQL| DB
```

---

## Aliran Data Real-Time (Inbound & Outbound)

Diagram urutan di bawah ini menjelaskan alur data sensor dari perangkat fisik IoT hingga masuk ke database dan dianalisis oleh model Machine Learning secara otomatis:

```mermaid
sequenceDiagram
    participant ESP32 as IoT ESP32 (Wokwi)
    participant MQTT as Mosquitto MQTT Broker
    participant NR as Node-RED Flow
    participant RMQ as RabbitMQ Queue
    participant Consumer as ML Consumer (Python)
    participant ML as ML Service (FastAPI)
    participant DB as MySQL DB
    
    loop Setiap 5 Detik
        ESP32->>MQTT: Publish data sensor (voltage, current, temperature)
        MQTT-->>NR: Meneruskan payload MQTT
        NR->>RMQ: Menerjemahkan & Publish Event AMQP (grid.new)
    end
    
    Consumer->>RMQ: Mengambil pesan dari antrean (dequeue)
    Consumer->>ML: POST /predict/grid-quality & /detect/anomaly
    ML-->>Consumer: Mengembalikan klasifikasi & skor anomali
    
    alt Terdeteksi Anomali
        Consumer->>DB: Simpan data gangguan ke tabel grid_incidents
        Consumer->>RMQ: Kirim notifikasi alarm ke warga (anomaly.alert)
    else Kondisi Normal
        Consumer->>DB: Simpan pembacaan sensor ke tabel grid_readings
    end
```
