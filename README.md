# Smart City Integrated Platform (Smart Energy & Power Grid)

This is the Smart City Integrated Platform, focusing on the **Smart Energy and Power Grid** sub-theme. The platform integrates an API Gateway, an OAuth 2.0 Authorization Server, multiple downstream PHP services, a Python Machine Learning Service, and an IoT event ingestion pipeline.

---

## 🗺️ Folder Layout

```plaintext
├── database/              # SQL schemas, seed scripts, and Postman API collection
│   ├── schema.sql         # Database schema definition
│   ├── seed.sql           # Database seed data (citizens, readings, client credentials)
│   └── smartcity_platform.postman_collection.json # API endpoints collection
├── express-gateway/       # API Gateway built with Node.js and Express
├── iot/                   # IoT simulations and configs
│   ├── simulator.py       # ESP32 Grid Sensor simulator
│   ├── mosquitto.conf     # Mosquitto MQTT broker configuration
│   └── node-red-data/     # Node-RED event processing flows
├── k8s/                   # Kubernetes manifests for orchestration
├── monitoring/            # Prometheus and Grafana dashboards for observability
├── oauth-server/          # OAuth 2.0 Authorization Server
├── php-citizen/           # Downstream PHP service managing citizen profiles and reports
├── php-grid/              # Downstream PHP service managing physical grid zones
├── php-power/             # Downstream PHP service managing power consumption metrics
├── python-ml-service/     # FastAPI service hosting ML models (Demand, Quality, Anomaly)
├── docker-compose.yml     # Core Docker Compose orchestration file
├── Makefile               # Helper commands for local environment management
└── README.md              # Project documentation (this file)
```

---

## ⚙️ Prerequisites

To run this platform, ensure you have the following installed:
- **Docker** (v20.10+) and **Docker Compose** (v2.0+)
- **Git**
- **Node.js** (v18+) & **npm** (for local/manual development of Node.js services)
- **PHP** (v8.1+) & **Composer** (for local/manual PHP service running)
- **Python** (v3.9+) & **pip** (for local/manual Python service running)

---

## 🚀 Local Setup Instructions

### 1. Environment Configuration
Copy the `.env.example` file to `.env` at the root level and configure the environment variables:
```bash
cp .env.example .env
```
Ensure database credentials, JWT secrets, and RabbitMQ connection info are set correctly in `.env`.

### 2. Launching Services with Docker Compose
To build and start all system services (MySQL, RabbitMQ, Mosquitto, API Gateway, OAuth Server, Downstream PHP Services, Python ML Service, Prometheus, and Grafana) in the background, run:
```bash
# Using Makefile
make up

# Alternatively, using docker compose directly
docker compose up -d --build
```

To view system logs:
```bash
make logs
```

To stop all services:
```bash
make down
```

---

## 🗄️ Database Seeding

The database will seed automatically during initialization, or can be seeded manually.

### Automatic Seeding
The MySQL container mounts `./database` to `/docker-entrypoint-initdb.d`. Upon first run:
1. `schema.sql` creates the database tables.
2. `seed.sql` populates initial data including zones, oauth clients, and citizen records.

### Manual Seeding
If you need to reset the database and reseed at any time, execute the following command:
```bash
docker exec -i smartcity-mysql mysql -u root -prootpass smartcity < database/seed.sql
```
*(Replace `rootpass` with your `DB_PASS` from `.env` if changed).*

---

## 🧪 Test Scenarios

### 1. Authentication & OAuth 2.0
The platform uses JWT-based OAuth 2.0. Users must first retrieve a token.

#### Password Grant (Citizen/Admin Logins)
Retrieve a token by sending a request to the API Gateway `/oauth/token` endpoint. All logins (including admin) query the database and verify user credentials (email or NIK as username, and NIK as password).
- **Request**:
  ```http
  POST http://localhost:3000/oauth/token
  Content-Type: application/json

  {
    "grant_type": "password",
    "username": "nik_or_email_here",
    "password": "nik_here"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "code": 200,
    "data": {
      "access_token": "eyJhbGciOi...",
      "token_type": "Bearer",
      "expires_in": 3600,
      "refresh_token": "..."
    },
    "message": "Token issued"
  }
  ```

#### Client Credentials Grant (Internal Services)
- **Request**:
  ```http
  POST http://localhost:3000/oauth/token
  Content-Type: application/json

  {
    "grant_type": "client_credentials",
    "client_id": "client_id_here",
    "client_secret": "client_secret_here"
  }
  ```

---

### 2. Role-Based Access Control (RBAC)

Downstream API resources verify user permissions through the API Gateway, which validates token roles:
- **`citizen`**: Can access public and citizen-owned data.
- **`admin`**: Full write access, including updating report statuses.

#### Scenario: Update Incident Report Status (Admin Only)
- **Unauthorized/Citizen Request** (fails):
  ```http
  PATCH http://localhost:3000/api/reports/1/status
  Authorization: Bearer <Citizen_Token>
  Content-Type: application/json

  {
    "status": "resolved"
  }
  ```
  *Response*: `403 Forbidden - Akses khusus Admin`

- **Authorized Admin Request** (succeeds):
  ```http
  PATCH http://localhost:3000/api/reports/1/status
  Authorization: Bearer <Admin_Token>
  Content-Type: application/json

  {
    "status": "resolved"
  }
  ```
  *Response*: `200 OK`

---

### 3. Rate-Limiting

The API Gateway enforces rate-limiting at two levels:
- **Global Rate Limiter**: Maximum 100 requests per 15 minutes per IP address. Unauthenticated requests that exceed this limit receive a `429 Too Many Requests`.
- **Authenticated Rate Limiter**: Maximum 500 requests per hour per authenticated client/user token.

---

### 4. Machine Learning Endpoints

The Python ML Service (`/predict`, `/detect`, `/model`) provides endpoints through the gateway for inference.

#### Power Demand Prediction (Regression)
Predict power demand for a zone.
- **Request**:
  ```http
  POST http://localhost:3000/predict/power
  Authorization: Bearer <token>
  Content-Type: application/json

  {
    "id": "req-001",
    "timestamp": "2026-06-27T12:00:00Z",
    "hour": 12,
    "day_of_week": 5,
    "temperature": 31.5,
    "prev_demand": 280.0,
    "zone": "Kawasan Industri A"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "code": 200,
    "data": {
      "id": "req-001",
      "timestamp": "2026-06-27T12:00:00Z",
      "predicted_demand_kw": 298.54,
      "status": "Normal"
    }
  }
  ```

#### Grid Quality Classification
Classify network quality status.
- **Request**:
  ```http
  POST http://localhost:3000/predict/grid-quality
  Authorization: Bearer <token>
  Content-Type: application/json

  {
    "id": "req-002",
    "timestamp": "2026-06-27T12:00:00Z",
    "zone": "Perumahan Griya",
    "voltage": 220.5,
    "current": 15.2,
    "power_factor": 0.95,
    "temperature": 28.0,
    "humidity": 65.0
  }
  ```

#### Anomaly Detection
Detect anomalies in sensor feeds.
- **Request**:
  ```http
  POST http://localhost:3000/detect/anomaly
  Authorization: Bearer <token>
  Content-Type: application/json

  {
    "id": "req-003",
    "timestamp": "2026-06-27T12:00:00Z",
    "zone": "Pusat Bisnis CBD",
    "sensor_value": 450.0,
    "timestamp_hour": 12,
    "rolling_mean_1h": 220.0,
    "z_score": 3.1
  }
  ```

#### Feature Importance
Fetch the trained Random Forest feature importance metrics.
- **Request**:
  ```http
  GET http://localhost:3000/model/feature-importance
  Authorization: Bearer <token>
  ```

#### Batch Power Demand Prediction
Predict power demands in batches.
- **Request**:
  ```http
  POST http://localhost:3000/predict/batch
  Authorization: Bearer <token>
  Content-Type: application/json

  {
    "requests": [
      {
        "id": "batch-1",
        "timestamp": "2026-06-27T12:00:00Z",
        "hour": 12,
        "day_of_week": 5,
        "temperature": 31.5,
        "prev_demand": 280.0,
        "zone": "Kawasan Industri A"
      }
    ]
  }
  ```
