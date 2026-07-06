/*
 * TITIK 1: ABSENSI (Dual RFID Reader) - PlatformIO Source
 * ----------------------------------------------------
 * Hardware: ESP32 DevKit v1
 * 
 * RFID #1 - MASUK:
 * - SDA (SS)  -> GPIO 5
 * - RST       -> GPIO 2
 * - SCK       -> GPIO 18 (Shared)
 * - MOSI      -> GPIO 23 (Shared)
 * - MISO      -> GPIO 19 (Shared)
 * 
 * RFID #2 - KELUAR:
 * - SDA (SS)  -> GPIO 32
 * - RST       -> GPIO 33
 * - SCK       -> GPIO 18 (Shared)
 * - MOSI      -> GPIO 23 (Shared)
 * - MISO      -> GPIO 19 (Shared)
 * 
 * LCD I2C (16x2):
 * - SDA       -> GPIO 21
 * - SCL       -> GPIO 22
 * - VCC       -> VIN (5V)
 * - GND       -> GND
 */

#include <Arduino.h>
#include <WiFi.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// --- WIFI & MQTT CONFIG ---
const char* ssid = "NAMA_WIFI_ANDA";         // Ganti dengan SSID Wi-Fi Anda
const char* password = "PASSWORD_WIFI_ANDA"; // Ganti dengan password Wi-Fi Anda
const char* mqtt_server = "broker.hivemq.com";
const int mqtt_port = 1883;

// Topic MQTT
const char* topic_tap = "taptrack/absensi/tap";
const char* topic_command = "taptrack/absensi/command";

// --- PIN DEFINITIONS ---
#define SS_1_PIN    5
#define RST_1_PIN   2
#define SS_2_PIN    32
#define RST_2_PIN   33

// --- DEVICE INITIALIZATION ---
MFRC522 mfrc522_1(SS_1_PIN, RST_1_PIN);
MFRC522 mfrc522_2(SS_2_PIN, RST_2_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long lastMqttReconnectAttempt = 0;

// --- FUNCTION DECLARATIONS ---
void setup_wifi();
bool reconnectMqtt();
void mqttCallback(char* topic, byte* payload, unsigned int length);
void handleRfidScan(MFRC522* rfid, String readerName);
void tampilkanStandby();
void refreshDevices();

void setup() {
  Serial.begin(115200);
  
  // SPI Bus & RFID
  SPI.begin();
  mfrc522_1.PCD_Init();
  mfrc522_2.PCD_Init();
  
  // LCD
  lcd.init();
  lcd.backlight();
  tampilkanStandby();

  // WiFi & MQTT
  setup_wifi();
  mqttClient.setServer(mqtt_server, mqtt_port);
  mqttClient.setCallback(mqttCallback);
}

void loop() {
  // Reconnect MQTT non-blocking
  if (!mqttClient.connected()) {
    unsigned long now = millis();
    if (now - lastMqttReconnectAttempt > 5000) {
      lastMqttReconnectAttempt = now;
      if (reconnectMqtt()) {
        lastMqttReconnectAttempt = 0;
      }
    }
  } else {
    mqttClient.loop();
  }

  // Scan RFID #1 (MASUK)
  if (mfrc522_1.PICC_IsNewCardPresent() && mfrc522_1.PICC_ReadCardSerial()) {
    handleRfidScan(&mfrc522_1, "masuk");
  }
  
  // Scan RFID #2 (KELUAR)
  if (mfrc522_2.PICC_IsNewCardPresent() && mfrc522_2.PICC_ReadCardSerial()) {
    handleRfidScan(&mfrc522_2, "keluar");
  }
}

void setup_wifi() {
  delay(10);
  Serial.print("Connecting to wifi: ");
  Serial.println(ssid);
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MENYAMBUNG WIFI ");

  WiFi.begin(ssid, password);
  int counter = 0;
  while (WiFi.status() != WL_CONNECTED && counter < 30) {
    delay(500);
    Serial.print(".");
    lcd.setCursor(counter % 16, 1);
    lcd.print(".");
    counter++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWi-Fi connected!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WIFI TERHUBUNG! ");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    delay(1500);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WIFI OFFLINE    ");
    delay(1500);
  }
  tampilkanStandby();
}

bool reconnectMqtt() {
  Serial.print("Connecting to MQTT...");
  String clientId = "ESP32Absensi-" + String(random(0xffff), HEX);
  if (mqttClient.connect(clientId.c_str())) {
    Serial.println("connected!");
    mqttClient.subscribe(topic_command);
    return true;
  } else {
    Serial.print("failed, rc=");
    Serial.println(mqttClient.state());
    return false;
  }
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String msg = "";
  for (unsigned int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }
  Serial.println("Message: " + msg);

  StaticJsonDocument<256> doc;
  DeserializationError error = deserializeJson(doc, msg);
  if (error) return;

  String status = doc["status"] | "";
  
  // Kami memfilter agar hanya memproses respon khusus absensi
  String reader = doc["reader"] | "";
  if (reader == "") return; 

  if (status == "success") {
    String name = doc["name"] | "KARYAWAN";
    String direction = doc["reader"] | "masuk";
    
    lcd.clear();
    lcd.setCursor(0, 0);
    if (direction == "masuk") {
      lcd.print(" TAP IN BERHASIL");
    } else {
      lcd.print("TAP OUT BERHASIL");
    }

    if (name.length() > 16) {
      name = name.substring(0, 16);
    }
    lcd.setCursor(0, 1);
    lcd.print(name);
    
    delay(3000);
    tampilkanStandby();
  } else if (status == "error") {
    String uid = doc["uid"] | "UNKNOWN";
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(" AKSES DITOLAK! ");
    lcd.setCursor(0, 1);
    lcd.print(uid);
    
    delay(2500);
    tampilkanStandby();
  }
}

void handleRfidScan(MFRC522* rfid, String readerName) {
  String strUID = "";
  for (byte i = 0; i < rfid->uid.size; i++) {
    strUID += String(rfid->uid.uidByte[i] < 0x10 ? "0" : "");
    strUID += String(rfid->uid.uidByte[i], HEX);
    if (i < rfid->uid.size - 1) strUID += " ";
  }
  strUID.toUpperCase();

  Serial.println("Scan (" + readerName + "): " + strUID);

  lcd.clear();
  lcd.setCursor(0, 0);
  if (readerName == "masuk") {
    lcd.print("   RFID MASUK   ");
  } else {
    lcd.print("  RFID KELUAR   ");
  }
  lcd.setCursor(0, 1);
  lcd.print("MENGECEK KARTU...");

  if (mqttClient.connected()) {
    StaticJsonDocument<128> doc;
    doc["uid"] = strUID;
    doc["reader"] = readerName;

    char buffer[128];
    serializeJson(doc, buffer);
    mqttClient.publish(topic_tap, buffer);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("SISTEM OFFLINE  ");
    delay(2000);
    tampilkanStandby();
  }

  rfid->PICC_HaltA();
  rfid->PCD_StopCrypto1();
}

void tampilkanStandby() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("  SISTEM ABSEN  ");
  lcd.setCursor(0, 1);
  lcd.print(" TEMPELKAN KARTU");
}

void refreshDevices() {
  delay(100);
  lcd.init();
  lcd.backlight();
  mfrc522_1.PCD_Init();
  mfrc522_2.PCD_Init();
}
