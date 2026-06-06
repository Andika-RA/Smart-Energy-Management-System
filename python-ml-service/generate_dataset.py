import pandas as pd
import numpy as np
import os
import random
from datetime import datetime, timedelta

def generate_datasets(num_records=6000):
    print("Current Working Directory:", os.getcwd())
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    DATA_DIR = os.path.join(BASE_DIR, "data")
    os.makedirs(DATA_DIR, exist_ok=True)
    print(f"Memulai generasi {num_records} baris data untuk 3 dataset Energi Pintar...")
    start_time = datetime(2025, 1, 1, 0, 0)
    zones = ["zone1", "zone2", "zone3", "zone4"]
    power_data = []
    for i in range(num_records):
        current_time = start_time + timedelta(hours=i)
        zone = zones[i % len(zones)]
        hour = current_time.hour
        day_of_week = current_time.weekday()
        temperature = round(random.uniform(24, 36), 1)
        prev_demand = round(random.uniform(100, 500), 2)
        base_demand = prev_demand * 0.8
        if 18 <= hour <= 22:
            base_demand += random.uniform(100, 200)
        if temperature > 32:
            base_demand += random.uniform(50, 100)
        power_demand = round(base_demand + random.gauss(0, 20), 2)
        power_data.append([hour, day_of_week, temperature, prev_demand, zone, max(10, power_demand)])
    df_power = pd.DataFrame(power_data, columns=['hour', 'day_of_week', 'temperature', 'prev_demand', 'zone', 'power_demand'])
    df_power.to_csv(os.path.join(DATA_DIR, "power_history.csv"), index=False)
    print("-> data/power_history.csv berhasil dibuat.")
    grid_data = []
    for i in range(num_records):
        voltage = round(random.gauss(220, 5), 2)
        current = round(random.uniform(5, 50), 2)
        power_factor = round(random.uniform(0.75, 0.99), 2)
        temperature = round(random.uniform(24, 36), 1)
        humidity = round(random.uniform(50, 90), 1)
        if voltage < 190 or voltage > 245 or power_factor < 0.80:
            status = "Critical"
        elif 190 <= voltage < 205 or 235 < voltage <= 245 or 0.80 <= power_factor < 0.85:
            status = "Warning"
        else:
            status = "Normal"
        grid_data.append([voltage, current, power_factor, temperature, humidity, status])
    df_grid = pd.DataFrame(grid_data, columns=['voltage', 'current', 'power_factor', 'temperature', 'humidity', 'grid_status'])
    df_grid.to_csv(os.path.join(DATA_DIR, "grid_quality.csv"), index=False)
    print("-> data/grid_quality.csv berhasil dibuat.")
    sensor_data = []
    for i in range(num_records):
        timestamp_hour = (start_time + timedelta(hours=i)).hour
        is_anomaly = random.random() < 0.05
        if is_anomaly:
            sensor_value = round(random.uniform(300, 500), 2)
        else:
            sensor_value = round(random.gauss(100, 15), 2)
        rolling_mean_1h = round(sensor_value + random.uniform(-10, 10), 2)
        z_score = round((sensor_value - 100) / 15, 2)
        sensor_data.append([sensor_value, timestamp_hour, rolling_mean_1h, z_score])
    df_sensors = pd.DataFrame(sensor_data, columns=['sensor_value', 'timestamp_hour', 'rolling_mean_1h', 'z_score'])
    df_sensors.to_csv(os.path.join(DATA_DIR, "energy_sensors.csv"), index=False)
    print("-> data/energy_sensors.csv berhasil dibuat.")

if __name__ == "__main__":
    generate_datasets(6000)