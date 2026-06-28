from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from typing import List
import joblib
import numpy as np
from datetime import datetime
import os
from prometheus_client import make_asgi_app, Counter

app = FastAPI(
    title="Smart Energy ML Service", 
    version="1.0", 
    description="Power Demand, Grid Quality & Anomaly Prediction"
)

anomaly_detections_total = Counter('anomaly_detections_total', 'Total Deteksi Anomali Fisik', ['job'])
metrics_app = make_asgi_app()
app.mount("/metrics", metrics_app)

MODEL_PATH = "models/smartcity_models.pkl"
try:
    BUNDLE = joblib.load(MODEL_PATH)
except Exception as e:
    BUNDLE = {}
    print(f"Warning: Model belum dilatih atau tidak ditemukan. Error: {e}")

def standard_response(status: str, code: int, data: dict | list | None, message: str):
    return {
        "status": status,
        "code": code,
        "data": data,
        "message": message,
        "timestamp": datetime.utcnow().isoformat() + "Z",
        "service": "python-ml"
    }

class PowerIn(BaseModel):
    id: str
    timestamp: str
    hour: int = Field(..., ge=0, le=23)
    day_of_week: int = Field(..., ge=0, le=6)
    temperature: float
    prev_demand: float = Field(..., ge=0)
    zone: str

class GridIn(BaseModel):
    id: str
    timestamp: str
    zone: str
    voltage: float = Field(..., ge=0)
    current: float = Field(..., ge=0)
    power_factor: float = Field(..., ge=0.0, le=1.0)
    temperature: float
    humidity: float = Field(..., ge=0, le=100)

class SensorIn(BaseModel):
    id: str
    timestamp: str
    zone: str
    sensor_value: float = Field(..., ge=0)
    timestamp_hour: int = Field(..., ge=0, le=23)
    rolling_mean_1h: float
    z_score: float

class BatchPowerIn(BaseModel):
    requests: List[PowerIn]

@app.get("/health")
def health():
    data = {"status": "ok", "models_loaded": list(BUNDLE.keys())}
    return standard_response("success", 200, data, "Service is healthy and ready")

@app.post("/predict/power")
def predict_power(d: PowerIn):
    if 'power_regression' not in BUNDLE:
        raise HTTPException(status_code=500, detail="Model Power Demand belum siap.")
    b = BUNDLE['power_regression']
    try:
        zone_enc = b['le_zone'].transform([d.zone])[0]
    except:
        zone_enc = 0
    X = b['scaler'].transform([[d.hour, d.day_of_week, d.temperature, d.prev_demand, zone_enc]])
    pred_demand = float(b['model'].predict(X)[0])
    data = {
        "id": d.id,
        "timestamp": d.timestamp,
        "predicted_demand_kw": round(pred_demand, 2),
        "status": "Tinggi" if pred_demand > 300 else "Normal"
    }
    return standard_response("success", 200, data, "Prediksi konsumsi daya berhasil")

@app.post("/predict/grid-quality")
def predict_grid_quality(d: GridIn):
    if 'grid_classification' not in BUNDLE:
        raise HTTPException(status_code=500, detail="Model Grid Quality belum siap.")
    b = BUNDLE['grid_classification']
    X = b['scaler'].transform([[d.voltage, d.current, d.power_factor, d.temperature, d.humidity]])
    pred = b['model'].predict(X)[0]
    proba = b['model'].predict_proba(X)[0]
    label = b['le_status'].inverse_transform([pred])[0]
    data = {
        "id": d.id,
        "timestamp": d.timestamp,
        "grid_status": label,
        "confidence": round(float(proba.max()), 3),
        "probabilities": dict(zip(b['le_status'].classes_.tolist(), proba.round(3).tolist()))
    }
    return standard_response("success", 200, data, "Klasifikasi kualitas jaringan berhasil")

@app.post("/detect/anomaly")
def detect_anomaly(d: SensorIn):
    if 'anomaly' not in BUNDLE:
        raise HTTPException(status_code=500, detail="Model Anomaly belum siap.")
    b = BUNDLE['anomaly']
    X = b['scaler'].transform([[d.sensor_value, d.timestamp_hour, d.rolling_mean_1h, d.z_score]])
    score = float(b['model'].score_samples(X)[0])
    is_anom = score < -0.1
    
    if is_anom:
        anomaly_detections_total.labels(job='python-ml').inc()

    data = {
        "id": d.id,
        "timestamp": d.timestamp,
        "is_anomaly": is_anom,
        "anomaly_score": round(-score, 4),
        "severity": "Kritis" if score < -0.3 else "Peringatan" if is_anom else "Normal"
    }
    return standard_response("success", 200, data, "Deteksi anomali berhasil")

@app.get("/model/feature-importance")
def get_feature_importance():
    if 'power_regression' not in BUNDLE:
        raise HTTPException(status_code=500, detail="Model belum siap.")
    b = BUNDLE['power_regression']
    importances = b['model'].feature_importances_
    data = {
        "model": "Random Forest Regressor (Power Demand)",
        "features": dict(zip(b['features'], importances.round(4).tolist()))
    }
    return standard_response("success", 200, data, "Feature importance berhasil diambil")

@app.post("/predict/batch")
def predict_batch_power(data: BatchPowerIn):
    if 'power_regression' not in BUNDLE:
        raise HTTPException(status_code=500, detail="Model Power Demand belum siap.")
    results = []
    b = BUNDLE['power_regression']
    for d in data.requests:
        try:
            zone_enc = b['le_zone'].transform([d.zone])[0]
        except:
            zone_enc = 0
        X = b['scaler'].transform([[d.hour, d.day_of_week, d.temperature, d.prev_demand, zone_enc]])
        pred = float(b['model'].predict(X)[0])
        results.append({"id": d.id, "zone": d.zone, "predicted_demand_kw": round(pred, 2)})
    return standard_response("success", 200, results, f"Batch prediksi untuk {len(results)} data berhasil")