import pika
import json
import joblib
import numpy as np
import time
import os
from datetime import datetime

def start_consumer():
    time.sleep(10)
    try:
        rabbitmq_host = os.getenv('RABBITMQ_HOST', 'localhost')
        conn = pika.BlockingConnection(pika.ConnectionParameters(host=rabbitmq_host))
        ch = conn.channel()
        print("[*] Memuat model Machine Learning dari models/smartcity_models.pkl ...")
        bundle = joblib.load("models/smartcity_models.pkl")
        b_anomaly = bundle['anomaly']
        ch.exchange_declare(exchange='city.events', exchange_type='topic', durable=True)
        ch.queue_declare(queue='energy.new', durable=True)
        ch.queue_bind(queue='energy.new', exchange='city.events', routing_key='energy.new')
        ch.queue_declare(queue='anomaly.alert', durable=True)
        ch.queue_bind(queue='anomaly.alert', exchange='city.events', routing_key='anomaly.alert')

        def callback(ch, method, props, body):
            try:
                event = json.loads(body)
                event_id = event.get('id', 'unknown-id')
                event_timestamp = event.get('timestamp', datetime.utcnow().isoformat() + "Z")
                sensor_val = event.get('sensor_value', 0)
                hour = event.get('timestamp_hour', 0)
                rolling_mean = event.get('rolling_mean_1h', sensor_val)
                z_score = event.get('z_score', 0)
                zone = event.get('zone', 'unknown')
                X = b_anomaly['scaler'].transform([[sensor_val, hour, rolling_mean, z_score]])
                score = float(b_anomaly['model'].score_samples(X)[0])
                is_anom = score < -0.1
                status_text = "ANOMALI DETECTED!" if is_anom else "Normal"
                print(f"[ML Consumer] ID: {event_id} | Zone: {zone} | Sensor: {sensor_val} | Status: {status_text} | Score: {score:.4f}")
                if is_anom:
                    alert_payload = {
                        "id": event_id,
                        "timestamp": event_timestamp,
                        "zone": zone,
                        "alert_type": "ENERGY_SPIKE",
                        "severity": "Kritis" if score < -0.3 else "Peringatan",
                        "sensor_value": sensor_val,
                        "message": f"Terdeteksi anomali tegangan/arus di {zone}"
                    }
                    ch.basic_publish(
                        exchange='city.events',
                        routing_key='anomaly.alert',
                        body=json.dumps(alert_payload)
                    )
                    print(f"  -> [Alert] Peringatan anomali dipublish ke 'anomaly.alert'!")
                ch.basic_ack(delivery_tag=method.delivery_tag)
            except Exception as e:
                print(f"[Error] Gagal memproses event: {e}")
                ch.basic_reject(delivery_tag=method.delivery_tag, requeue=False)
        ch.basic_consume(queue='energy.new', on_message_callback=callback)
        print("[*] ML Consumer listening on queue 'energy.new'...")
        ch.start_consuming()
    except Exception as e:
        print(f"[Fatal] Gagal terhubung ke RabbitMQ: {e}")

if __name__ == "__main__":
    start_consumer()