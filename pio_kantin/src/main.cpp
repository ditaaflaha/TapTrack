/*
 * TITIK 3: KANTIN (Single RFID Reader for Flat 12K Purchases) - PlatformIO Source
 * ----------------------------------------------------------------------------
 * Hardware: ESP32 DevKit v1
 * 
 * RFID RC-522:
 * - SDA (SS)  -> GPIO 5
 * - RST       -> GPIO 4
 * - SCK       -> GPIO 18
 * - MOSI      -> GPIO 23
 * - MISO      -> GPIO 19
 * 
 * LCD I2C (16x2):
 * - SDA       -> GPIO 21
 * - SCL       -> GPIO 22
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
const char* topic_tap = "taptrack/kantin/tap";
const char* topic_command = "taptrack/kantin/command";

// --- PIN DEFINITIONS ---
#define SS_PIN   5
#define RST_PIN  4 // Sesuai diagram wiring Kantin: RST terhubung ke D4!

// --- DEVICE INITIALIZATION ---
MFRC522 mfrc522(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long lastMqttReconnectAttempt = 0;

// --- FUNCTION DECLARATIONS ---
void setup_wifi();
bool reconnectMqtt();
void mqttCallback(char* topic, byte* payload, unsigned int length);
void handleRfidScan();
void tampilkanStandby();

void setup() {
  Serial.begin(115200);
  
  // SPI Bus & RFID
  SPI.begin();
  mfrc522.PCD_Init();
  
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

  // Scan RFID
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    handleRfidScan();
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
  String clientId = "ESP32Kantin-" + String(random(0xffff), HEX);
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

  // 1. Transaksi Sukses
  if (status == "success") {
    String name = doc["name"] | "KARYAWAN";
    String info = doc["info"] | "Sukses";
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("TRANSAKSI SUKSES");
    
    // Tampilkan info transaksi (misal: "Tap Jajan ke-3" atau Nama)
    if (info.length() > 16) {
      info = info.substring(0, 16);
    }
    lcd.setCursor(0, 1);
    lcd.print(info);

    delay(3000);
    tampilkanStandby();
  } 
  
  // 2. Kartu Asing / Error
  else if (status == "error") {
    String uid = doc["uid"] | "UNKNOWN";
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(" KARTU ASING!  ");
    lcd.setCursor(0, 1);
    lcd.print(uid);
    
    delay(2500);
    tampilkanStandby();
  }
}

void handleRfidScan() {
  String strUID = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    strUID += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    strUID += String(mfrc522.uid.uidByte[i], HEX);
    if (i < mfrc522.uid.size - 1) strUID += " ";
  }
  strUID.toUpperCase();

  Serial.println("Scan Kantin: " + strUID);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("   TAP KANTIN   ");
  lcd.setCursor(0, 1);
  lcd.print("MENGECEK KARTU...");

  if (mqttClient.connected()) {
    StaticJsonDocument<128> doc;
    doc["uid"] = strUID;

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

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}

void tampilkanStandby() {
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("   TAP KANTIN   ");
  lcd.setCursor(0, 1);
  lcd.print("Rp 12.000 / TAP ");
}
