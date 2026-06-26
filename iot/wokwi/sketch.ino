#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <DHTesp.h>
#include "time.h"
#include <sys/time.h>
#include "secrets.h" 

const char* ssid = SECRET_SSID;
const char* password = SECRET_PASS;
const char* mqtt_server = SECRET_MQTT_BROKER; 
const char* ntpServer = "pool.ntp.org";
const long  gmtOffset_sec = 25200;
const int   daylightOffset_sec = 0;

WiFiClient espClient;
PubSubClient client(espClient);
DHTesp dht;

#define DHT_PIN 15
#define SENSOR_VOLTAGE_PIN 36
#define SENSOR_CURRENT_Z1 32
#define SENSOR_CURRENT_Z2 33
#define SENSOR_CURRENT_Z3 34
#define SENSOR_CURRENT_Z4 35

void setup_wifi() {
  Serial.println("Menghubungkan ke WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500); Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung!");
}

void reconnect() {
  while (!client.connected()) {
    String clientId = "ESP32-SmartCity-" + String(random(0xffff), HEX);
    if (client.connect(clientId.c_str())) {
      Serial.println("MQTT Terhubung!");
    } else {
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  dht.setup(DHT_PIN, DHTesp::DHT22);
  pinMode(SENSOR_VOLTAGE_PIN, INPUT);
  pinMode(SENSOR_CURRENT_Z1, INPUT);
  pinMode(SENSOR_CURRENT_Z2, INPUT);
  pinMode(SENSOR_CURRENT_Z3, INPUT);
  pinMode(SENSOR_CURRENT_Z4, INPUT);
  setup_wifi();
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
  Serial.println("Sinkronisasi waktu...");
  client.setServer(mqtt_server, 1883);
}

void loop() {
  if (!client.connected()) reconnect();
  client.loop();
  struct timeval tv;
  gettimeofday(&tv, NULL);
  struct tm* timeinfo = localtime(&tv.tv_sec);
  int current_hour = 12, current_min = 0, current_sec = 0, current_ms = 0;
  char timeString[30] = "12:00:00.000 WIB";
  if (timeinfo != NULL) {
    current_hour = timeinfo->tm_hour;
    current_min = timeinfo->tm_min;
    current_sec = timeinfo->tm_sec;
    current_ms = tv.tv_usec / 1000;
    sprintf(timeString, "%02d:%02d:%02d.%03d WIB", current_hour, current_min, current_sec, current_ms);
  }
  float phys_temp = dht.getTemperature();
  float phys_hum = dht.getHumidity();
  if (isnan(phys_temp)) phys_temp = 30.0;
  if (isnan(phys_hum)) phys_hum = 70.0;
  int raw_voltage = analogRead(SENSOR_VOLTAGE_PIN);
  float actual_voltage = 200.0 + (raw_voltage / 4095.0) * 40.0;
  int raw_current[4] = {
    analogRead(SENSOR_CURRENT_Z1),
    analogRead(SENSOR_CURRENT_Z2),
    analogRead(SENSOR_CURRENT_Z3),
    analogRead(SENSOR_CURRENT_Z4)
  };
  Serial.println("\n=== MENGIRIM DATA SENSOR (JAM AKTUAL: " + String(timeString) + ") ===");
  for (int i = 0; i < 4; i++) {
    int zone_id = i + 1;
    String currentZone = "zone" + String(zone_id);
    float zone_max_capacity = 0.0;
    float time_modifier = 1.0;
    if (zone_id == 1) {
      zone_max_capacity = 50.0;
      if (current_hour >= 18 || current_hour <= 5) time_modifier = 1.0;
      else time_modifier = 0.4;
    } 
    else if (zone_id == 2) {
      zone_max_capacity = 20.0;
      if (current_hour >= 18 && current_hour <= 22) time_modifier = 1.0;
      else time_modifier = 0.3;
    } 
    else if (zone_id == 3) {
      zone_max_capacity = 100.0;
      if (current_hour >= 8 && current_hour <= 18) time_modifier = 1.0;
      else time_modifier = 0.2;
    } 
    else if (zone_id == 4) {
      zone_max_capacity = 300.0;
      if (current_hour == 12 || current_hour == 18) time_modifier = 0.7;
      else time_modifier = 1.0;
    }
    float sensor_ratio = raw_current[i] / 4095.0; 
    float actual_current = sensor_ratio * zone_max_capacity * time_modifier;
    if (phys_temp > 30.0) {
      actual_current *= 1.10;
    }
    float power_factor = 0.99 - (sensor_ratio * 0.15);
    if (zone_id == 4) power_factor -= 0.05;
    float power_demand = (actual_voltage * actual_current * power_factor) / 1000.0;
    bool is_physical_spike = (sensor_ratio > 0.95);
    float sensor_val = actual_current * 5.0;
    if (is_physical_spike) {
      actual_voltage -= 40.0;
      sensor_val *= 4.0;
      if (zone_id == 1) Serial.println("!!! SENSOR MENDETEKSI LONJAKAN ARUS FISIK !!!");
    }
    StaticJsonDocument<200> docGrid;
    docGrid["zone"] = currentZone;
    docGrid["voltage"] = actual_voltage;
    docGrid["current"] = actual_current;
    docGrid["power_factor"] = power_factor;
    docGrid["temperature"] = phys_temp;
    docGrid["humidity"] = phys_hum;
    char gridBuffer[256];
    serializeJson(docGrid, gridBuffer);
    client.publish(("city/" + currentZone + "/grid").c_str(), gridBuffer);
    StaticJsonDocument<200> docPower;
    docPower["zone"] = currentZone;
    docPower["temperature"] = phys_temp;
    docPower["prev_demand"] = power_demand; 
    char powerBuffer[256];
    serializeJson(docPower, powerBuffer);
    client.publish(("city/" + currentZone + "/power").c_str(), powerBuffer);
    StaticJsonDocument<200> docSensor;
    docSensor["zone"] = currentZone;
    docSensor["sensor_value"] = sensor_val;
    docSensor["rolling_mean_1h"] = (sensor_ratio < 0.95) ? sensor_val : (sensor_val / 4.0); 
    docSensor["z_score"] = is_physical_spike ? 5.5 : 0.5;
    char sensorBuffer[256];
    serializeJson(docSensor, sensorBuffer);
    client.publish(("city/" + currentZone + "/sensor").c_str(), sensorBuffer);
    Serial.println("[" + currentZone + "] V: " + String(actual_voltage) + "V | I: " + String(actual_current) + "A | Load: " + String(power_demand) + "kW | Fact: " + String(power_factor) + " | T: " + String(phys_temp) + "°C | H: " + String(phys_hum));
    delay(100);
  }
  
  delay(3000);
}