import pandas as pd
import numpy as np
import os
import random
from datetime import datetime, timedelta

def generate_id(prefix, current_time, zone, sequence):
    zone_number = zone.replace("zone", "")
    date_part = current_time.strftime("%Y%m%d")
    return f"{prefix}-{date_part}-{zone_number}{sequence:06d}"

def generate_datasets(num_records=6000):
    print("Current Working Directory:", os.getcwd())
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    DATA_DIR = os.path.join(BASE_DIR, "data")
    os.makedirs(DATA_DIR, exist_ok=True)
    print(f"Memulai generasi {num_records} baris data per zona untuk 3 dataset Energi Pintar...")
    start_time = datetime(2025, 9, 1, 0, 0)
    zones = ["zone1", "zone2", "zone3", "zone4"]

    power_data = []
    for i in range(num_records):
        current_time = start_time + timedelta(hours=i)
        timestamp_str = current_time.isoformat() + "Z"
        hour = current_time.hour
        day_of_week = current_time.weekday()
        if 8 <= hour <= 17:
            temperature = round(random.uniform(26, 36), 1)
        else:
            temperature = round(random.uniform(20, 28), 1)
        for zone in zones:
            record_id = generate_id("PWR", current_time, zone, i + 1)
            if zone == "zone1":
                base_demand = random.uniform(150, 250)
                if 18 <= hour <= 23 or 5 <= hour <= 8:
                    base_demand += random.uniform(100, 150)
                if temperature > 30:
                    base_demand += random.uniform(50, 80)
            elif zone == "zone2":
                base_demand = random.uniform(50, 100)
                if 18 <= hour <= 22:
                    base_demand += random.uniform(50, 80)
            elif zone == "zone3":
                base_demand = random.uniform(80, 150)
                if day_of_week < 5 and 8 <= hour <= 18:
                    base_demand += random.uniform(200, 400)
                if temperature > 32 and (8 <= hour <= 18):
                    base_demand += random.uniform(80, 120)
            elif zone == "zone4":
                base_demand = random.uniform(800, 1200)
                if day_of_week < 5:
                    base_demand += random.uniform(300, 500)
            power_demand = round(base_demand + random.gauss(0, 20), 2)
            prev_demand = round(base_demand * 0.95 + random.gauss(0, 15), 2) 
            power_data.append([record_id, timestamp_str, hour, day_of_week, temperature, max(10, prev_demand), zone, max(10, power_demand)])
    df_power = pd.DataFrame(power_data, columns=['id', 'timestamp', 'hour', 'day_of_week', 'temperature', 'prev_demand', 'zone', 'power_demand'])
    df_power.to_csv(os.path.join(DATA_DIR, "power_history.csv"), index=False)
    print("-> data/power_history.csv berhasil dibuat.")

    grid_data = []
    for i in range(num_records):
        current_time = start_time + timedelta(hours=i)
        timestamp_str = current_time.isoformat() + "Z"
        temperature = round(random.uniform(24, 36), 1)
        humidity = round(random.uniform(50, 90), 1)
        for zone in zones:
            record_id = generate_id("GRD", current_time, zone, i + 1)
            if zone == "zone4":
                voltage = round(random.gauss(215, 8), 2)
                current = round(random.uniform(100, 300), 2)
                power_factor = round(random.uniform(0.70, 0.92), 2)
            elif zone == "zone3":
                voltage = round(random.gauss(220, 4), 2)
                current = round(random.uniform(40, 100), 2)
                power_factor = round(random.uniform(0.85, 0.95), 2)
            elif zone == "zone1":
                voltage = round(random.gauss(220, 3), 2)
                current = round(random.uniform(15, 50), 2)
                power_factor = round(random.uniform(0.90, 0.99), 2)
            else:
                voltage = round(random.gauss(218, 5), 2)
                current = round(random.uniform(5, 25), 2)
                power_factor = round(random.uniform(0.80, 0.95), 2)
            if voltage < 190 or voltage > 245 or power_factor < 0.80:
                status = "Critical"
            elif 190 <= voltage < 205 or 235 < voltage <= 245 or 0.80 <= power_factor < 0.85:
                status = "Warning"
            else:
                status = "Normal"
            grid_data.append([record_id, timestamp_str, voltage, current, power_factor, temperature, humidity, zone, status])
    df_grid = pd.DataFrame(grid_data, columns=['id', 'timestamp', 'voltage', 'current', 'power_factor', 'temperature', 'humidity', 'zone', 'grid_status'])
    df_grid.to_csv(os.path.join(DATA_DIR, "grid_quality.csv"), index=False)
    print("-> data/grid_quality.csv berhasil dibuat.")

    sensor_data = []
    for i in range(num_records):
        current_time = start_time + timedelta(hours=i)
        timestamp_str = current_time.isoformat() + "Z"
        timestamp_hour = current_time.hour
        for zone in zones:
            record_id = generate_id("SNS", current_time, zone, i + 1)
            if zone == "zone4":
                base_sensor = 800
                anomaly_chance = 0.10
            elif zone == "zone3":
                base_sensor = 250
                anomaly_chance = 0.05
            elif zone == "zone1":
                base_sensor = 150
                anomaly_chance = 0.03
            else:
                base_sensor = 80
                anomaly_chance = 0.04
            is_anomaly = random.random() < anomaly_chance
            if is_anomaly:
                sensor_value = round(random.uniform(base_sensor * 1.5, base_sensor * 2.5), 2)
            else:
                sensor_value = round(random.gauss(base_sensor, base_sensor * 0.1), 2)
            rolling_mean_1h = round(sensor_value + random.uniform(-10, 10), 2)
            z_score = round((sensor_value - base_sensor) / (base_sensor * 0.1), 2)
            sensor_data.append([record_id, timestamp_str, sensor_value, timestamp_hour, rolling_mean_1h, zone, z_score])
    df_sensors = pd.DataFrame(sensor_data, columns=['id', 'timestamp', 'sensor_value', 'timestamp_hour', 'rolling_mean_1h', 'zone', 'z_score'])
    df_sensors.to_csv(os.path.join(DATA_DIR, "energy_sensors.csv"), index=False)
    print("-> data/energy_sensors.csv berhasil dibuat.")

if __name__ == "__main__":
    generate_datasets(6000)