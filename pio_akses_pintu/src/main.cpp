/*
 * FILE INI SUDAH DIARSIPKAN.
 *
 * Firmware aktif sekarang dibagi menjadi dua file terpisah:
 *   - main_masuk.cpp  -> Upload ke ESP32 yang dipasang di PINTU MASUK
 *   - main_keluar.cpp -> Upload ke ESP32 yang dipasang di PINTU KELUAR
 *
 * Gunakan perintah PlatformIO berikut untuk upload:
 *   pio run -e masuk  -t upload   (untuk ESP Masuk)
 *   pio run -e keluar -t upload   (untuk ESP Keluar)
 *
 * File ini tidak dikompilasi dan hanya disimpan sebagai referensi.
 */

/*
 * TITIK 2: AKSES PINTU (Single RFID + 2-Channel Solenoid Relay) - PlatformIO Source
 * ----------------------------------------------------------------------------
 * Hardware: ESP32 DevKit v1
 * 
 * RFID RC-522:
 * - SDA (SS)  -> GPIO 5
 * - RST       -> GPIO 2
 * - SCK       -> GPIO 18
 * - MOSI      -> GPIO 23
 * - MISO      -> GPIO 19
 * 
 * LCD I2C (16x2):
 * - SDA       -> GPIO 21
 * - SCL       -> GPIO 22
 * 
 * Relay 2-Channel:
 * - IN1       -> GPIO 25 (Solenoid #1 - Pintu Masuk)
 * - IN2       -> GPIO 26 (Solenoid #2 - Pintu Keluar)
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
const char* topic_tap = "taptrack/akses_pintu/tap";
const char* topic_command = "taptrack/akses_pintu/command";

// --- PIN DEFINITIONS ---
#define SS_PIN       5
#define RST_PIN      2
#define RELAY_1_PIN  25
#define RELAY_2_PIN  26

// --- DEVICE INITIALIZATION ---
MFRC522 mfrc522(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long lastMqttReconnectAttempt = 0;
bool gateUnlocked = false; // Status pintu dibuka bebas dari dashboard

// --- FUNCTION DECLARATIONS ---
void setup_wifi();
bool reconnectMqtt();
void mqttCallback(char* topic, byte* payload, unsigned int length);
void handleRfidScan();
void tampilkanStandby();
void refreshDevices();

void setup() {
  Serial.begin(115200);
  
  // SPI Bus & RFID
  SPI.begin();
  mfrc522.PCD_Init();
  
  // Relay Pins
  pinMode(RELAY_1_PIN, OUTPUT);
  pinMode(RELAY_2_PIN, OUTPUT);
  digitalWrite(RELAY_1_PIN, LOW); // Terkunci (Active HIGH relay)
  digitalWrite(RELAY_2_PIN, LOW); // Terkunci

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

  // Scan RFID (Hanya aktif jika pintu tidak diset Buka Bebas)
  if (!gateUnlocked) {
    if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
      handleRfidScan();
    }
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
  String clientId = "ESP32AksesPintu-" + String(random(0xffff), HEX);
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

  // 1. Sukses Memverifikasi Kartu
  if (status == "success") {
    String name = doc["name"] | "KARYAWAN";
    int relayCh = doc["relay_ch"] | 1;
    String dir = doc["direction"] | "masuk";
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("AKSES DITERIMA !");
    
    if (name.length() > 16) {
      name = name.substring(0, 16);
    }
    lcd.setCursor(0, 1);
    lcd.print(name);

    // Aktifkan Relay Pintu yang Sesuai
    int targetPin = (relayCh == 2) ? RELAY_2_PIN : RELAY_1_PIN;
    digitalWrite(targetPin, HIGH);
    Serial.printf("Membuka Pintu (Relay CH%d) untuk %s\n", relayCh, name.c_str());

    // Biarkan solenoid menyala selama 3 detik
    if (!gateUnlocked) {
      delay(3000);
      digitalWrite(targetPin, LOW); // Kunci kembali
    }

    refreshDevices();
    tampilkanStandby();
  } 
  
  // 2. Akses Ditolak
  else if (status == "error") {
    String uid = doc["uid"] | "UNKNOWN";
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(" ACCESS DENIED! ");
    lcd.setCursor(0, 1);
    lcd.print(uid);
    
    delay(2500);
    tampilkanStandby();
  } 
  
  // 3. Kendali Manual dari Dashboard
  else if (status == "control") {
    String action = doc["action"] | "";
    if (action == "unlock") {
      gateUnlocked = true;
      digitalWrite(RELAY_1_PIN, HIGH);
      digitalWrite(RELAY_2_PIN, HIGH);
      Serial.println("🔓 GERBANG DISET BEBAS (UNLOCKED)");
      tampilkanStandby();
    } else if (action == "lock") {
      gateUnlocked = false;
      digitalWrite(RELAY_1_PIN, LOW);
      digitalWrite(RELAY_2_PIN, LOW);
      Serial.println("🔒 GERBANG DISET KUNCI (LOCKED)");
      tampilkanStandby();
    }
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

  Serial.println("Scan Akses: " + strUID);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("  AKSES GERBANG  ");
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
  if (gateUnlocked) {
    lcd.setCursor(0, 0);
    lcd.print("  SISTEM AKTIF  ");
    lcd.setCursor(0, 1);
    lcd.print("  AKSES BEBAS   ");
  } else {
    lcd.setCursor(0, 0);
    lcd.print("  AKSES GERBANG ");
    lcd.setCursor(0, 1);
    lcd.print(" TEMPELKAN KARTU");
  }
}

void refreshDevices() {
  delay(100);
  lcd.init();
  lcd.backlight();
  mfrc522.PCD_Init();
}
