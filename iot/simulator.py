import paho.mqtt.client as mqtt
import json
import time
import random
from datetime import datetime

BROKER = "mosquitto"
ZONES = ["zone1", "zone2", "zone3", "zone4"]

client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION1)

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("Simulator terhubung ke Mosquitto Local Broker")
    else:
        print(f"Gagal terhubung, kode: {rc}")

client.on_connect = on_connect

while True:
    try:
        client.connect(BROKER, 1883)
        break
    except Exception as e:
        print(f"Menunggu broker MQTT... Error: {e}")
        time.sleep(5)

client.loop_start()

def generate_id(prefix, current_time, zone, sequence):
    zone_number = zone.replace("zone", "")
    date_part = current_time.strftime("%Y%m%d")
    return f"{prefix}-{date_part}-{zone_number}{sequence:06d}"

print("Memulai pengiriman data sensor periodik untuk 4 zona...")
sequence_counter = 1

try:
    while True:
        now = datetime.utcnow()
        timestamp_str = now.isoformat() + "Z"
        current_hour = now.hour
        day_of_week = now.weekday()
        for zone in ZONES:
            grid_payload = {
                "id": generate_id("GRD", now, zone, sequence_counter),
                "timestamp": timestamp_str,
                "zone": zone,
                "voltage": round(random.gauss(220, 2), 2),
                "current": round(random.uniform(5, 40), 2),
                "power_factor": round(random.uniform(0.85, 0.99), 2),
                "temperature": round(random.uniform(25, 35), 1),
                "humidity": round(random.uniform(50, 80), 1)
            }
            client.publish(f"city/{zone}/grid", json.dumps(grid_payload), qos=1)
            power_payload = {
                "id": generate_id("PWR", now, zone, sequence_counter),
                "timestamp": timestamp_str,
                "zone": zone,
                "hour": current_hour,
                "day_of_week": day_of_week,
                "temperature": grid_payload["temperature"],
                "prev_demand": round(random.uniform(100, 500), 2)
            }
            client.publish(f"city/{zone}/power", json.dumps(power_payload), qos=1)
            sensor_val = round(random.uniform(300, 500), 2) if random.random() < 0.05 else round(random.gauss(100, 10), 2)
            sensor_payload = {
                "id": generate_id("SNS", now, zone, sequence_counter),
                "timestamp": timestamp_str,
                "zone": zone,
                "sensor_value": sensor_val,
                "timestamp_hour": current_hour,
                "rolling_mean_1h": round(sensor_val + random.uniform(-5, 5), 2),
                "z_score": round((sensor_val - 100) / 10, 2)
            }
            client.publish(f"city/{zone}/sensor", json.dumps(sensor_payload), qos=1)
            print(f"[{now.strftime('%H:%M:%S')}] Publish {zone} -> Grid, Power & Sensor terkirim. (Seq: {sequence_counter})")
        sequence_counter += 1
        time.sleep(30)
except KeyboardInterrupt:
    print("\nSimulator dihentikan.")
    client.loop_stop()
    client.disconnect()