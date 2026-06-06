import pandas as pd
import numpy as np
import joblib
import os
from sklearn.ensemble import RandomForestRegressor, GradientBoostingClassifier, IsolationForest
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import r2_score, classification_report, accuracy_score

def train_and_evaluate():
    print("Memuat 3 dataset Energi Pintar...")
    df_power = pd.read_csv("data/power_history.csv")
    df_grid = pd.read_csv("data/grid_quality.csv")
    df_sensors = pd.read_csv("data/energy_sensors.csv")
    os.makedirs("models", exist_ok=True)
    print("\n--- MODEL 1: REGRESI ---")
    POWER_FEATS = ['hour', 'day_of_week', 'temperature', 'prev_demand', 'zone_enc']
    le_zone = LabelEncoder()
    df_power['zone_enc'] = le_zone.fit_transform(df_power['zone'])
    scaler_p = StandardScaler()
    X_p = scaler_p.fit_transform(df_power[POWER_FEATS])
    y_p = df_power['power_demand']
    X_train_p, X_test_p, y_train_p, y_test_p = train_test_split(X_p, y_p, test_size=0.2, random_state=42)
    mdl_p = RandomForestRegressor(n_estimators=200, max_depth=12, random_state=42)
    mdl_p.fit(X_train_p, y_train_p)
    cv_p = cross_val_score(mdl_p, X_p, y_p, cv=5, scoring='r2')
    y_pred_p = mdl_p.predict(X_test_p)
    print(f"Cross-Val R^2 Score: {cv_p.mean():.4f} (+/- {cv_p.std():.4f})")
    print(f"Test Set R^2 Score : {r2_score(y_test_p, y_pred_p):.4f}")
    print("\n--- MODEL 2: KLASIFIKASI MULTI-KELAS ---")
    GRID_FEATS = ['voltage', 'current', 'power_factor', 'temperature', 'humidity']
    le_status = LabelEncoder()
    y_g = le_status.fit_transform(df_grid['grid_status'])
    scaler_g = StandardScaler()
    X_g = scaler_g.fit_transform(df_grid[GRID_FEATS])
    X_train_g, X_test_g, y_train_g, y_test_g = train_test_split(X_g, y_g, test_size=0.2, random_state=42)
    mdl_g = GradientBoostingClassifier(n_estimators=150, learning_rate=0.1, random_state=42)
    mdl_g.fit(X_train_g, y_train_g)
    cv_g = cross_val_score(mdl_g, X_g, y_g, cv=5, scoring='accuracy')
    y_pred_g = mdl_g.predict(X_test_g)
    print(f"Cross-Val Accuracy : {cv_g.mean():.4f} (+/- {cv_g.std():.4f})")
    print("Classification Report (Test Set):")
    print(classification_report(y_test_g, y_pred_g, target_names=le_status.classes_))
    print("\n--- MODEL 3: DETEKSI ANOMALI ---")
    ANOMALY_FEATS = ['sensor_value', 'timestamp_hour', 'rolling_mean_1h', 'z_score']
    scaler_s = StandardScaler()
    X_s = scaler_s.fit_transform(df_sensors[ANOMALY_FEATS])
    mdl_s = IsolationForest(n_estimators=200, contamination=0.05, random_state=42)
    mdl_s.fit(X_s)
    pred_anomalies = mdl_s.predict(X_s)
    anomaly_count = np.sum(pred_anomalies == -1)
    print(f"Total data diproses : {len(X_s)}")
    print(f"Anomali terdeteksi  : {anomaly_count} ({(anomaly_count/len(X_s))*100:.2f}%)")
    print("\nMenyimpan seluruh objek ML ke models/smartcity_models.pkl...")
    joblib.dump({
        'power_regression': {
            'model': mdl_p, 
            'scaler': scaler_p, 
            'le_zone': le_zone, 
            'features': POWER_FEATS
        },
        'grid_classification': {
            'model': mdl_g, 
            'scaler': scaler_g, 
            'le_status': le_status, 
            'features': GRID_FEATS
        },
        'anomaly': {
            'model': mdl_s, 
            'scaler': scaler_s, 
            'features': ANOMALY_FEATS
        }
    }, 'models/smartcity_models.pkl')
    print("Berhasil! File models/smartcity_models.pkl siap digunakan.")

if __name__ == "__main__":
    train_and_evaluate()