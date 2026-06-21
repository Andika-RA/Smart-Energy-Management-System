import paho.mqtt.client as mqtt
import json
import os
from datetime import datetime
from dotenv import load_dotenv

load_dotenv()

BROKER = os.getenv("MQTT_BROKER", "mosquitto")
PORT = int(os.getenv("MQTT_PORT", 1883))
TOPICS = [("city/+/grid", 0), ("city/+/power", 0), ("city/+/sensor", 0)]

client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION1)

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print(f"Berhasil terhubung ke Broker ({BROKER}:{PORT})!")
        client.subscribe(TOPICS)
        print(f"Mendengarkan aliran data Smart City dari Wokwi...\n" + "="*50)
    else:
        print(f"Gagal terhubung, kode: {rc}")

def on_message(client, userdata, msg):
    try:
        now = datetime.now().strftime("%H:%M:%S")
        topic = msg.topic
        payload = json.loads(msg.payload.decode('utf-8'))
        print(f"[{now}] TOPIC: {topic}")
        print(json.dumps(payload, indent=2))
        print("-" * 50)
    except Exception as e:
        print(f"Gagal memproses pesan: {e}")

client.on_connect = on_connect
client.on_message = on_message

print(f"Menghubungkan ke broker {BROKER}...")
try:
    client.connect(BROKER, PORT, 60)
    client.loop_forever()
except KeyboardInterrupt:
    print("\nMonitoring dihentikan.")
    client.disconnect()
except Exception as e:
    print(f"Error koneksi: {e}")