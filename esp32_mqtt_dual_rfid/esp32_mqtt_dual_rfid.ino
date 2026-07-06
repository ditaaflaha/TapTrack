/*
 * ESP32 DUAL RFID (MASUK & KELUAR) + MQTT + LCD I2C SYSTEM
 * --------------------------------------------------------
 * Deskripsi:
 * Projek ini mengendalikan rolling door menggunakan dua sensor RFID RC522:
 * 1. RFID #1 (MASUK) - Mengirim data dengan tag "masuk"
 * 2. RFID #2 (KELUAR) - Mengirim data dengan tag "keluar"
 * 
 * Data tap dikirim ke Broker MQTT untuk divalidasi oleh Laravel.
 * Respon dari Laravel (akses diterima/ditolak) atau kendali manual
 * dari dashboard dibaca via MQTT untuk mengaktifkan Relay Rolling Door.
 *
 * Wiring Koneksi ESP32 DevKit v1:
 * 
 * RFID #1 - MASUK:
 * - SDA (SS)  -> GPIO 5
 * - RST       -> GPIO 14
 * - SCK       -> GPIO 18 (Parallel)
 * - MOSI      -> GPIO 23 (Parallel)
 * - MISO      -> GPIO 19 (Parallel)
 * - 3.3V      -> 3V3 (Parallel)
 * - GND       -> GND (Parallel)
 * 
 * RFID #2 - KELUAR:
 * - SDA (SS)  -> GPIO 32
 * - RST       -> GPIO 33
 * - SCK       -> GPIO 18 (Parallel)
 * - MOSI      -> GPIO 23 (Parallel)
 * - MISO      -> GPIO 19 (Parallel)
 * - 3.3V      -> 3V3 (Parallel)
 * - GND       -> GND (Parallel)
 * 
 * Relay Rolling Door:
 * - Pin Signal -> GPIO 25 (Active HIGH, ganti logic jika Relay Active LOW)
 * 
 * I2C LCD (16x2):
 * - SDA       -> GPIO 21
 * - SCL       -> GPIO 22
 * - VCC       -> 5V
 * - GND       -> GND
 * 
 * Libraries yang dibutuhkan di Arduino IDE:
 * 1. MFRC522 (oleh Miguel Balboa)
 * 2. LiquidCrystal_I2C (oleh Frank de Brabander)
 * 3. PubSubClient (oleh Nick O'Leary)
 * 4. ArduinoJson (oleh Benoit Blanchon)
 */

#include <WiFi.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// --- KONFIGURASI WIFI & MQTT ---
const char* ssid = "NAMA_WIFI_ANDA";         // Ganti dengan SSID Wi-Fi Anda
const char* password = "PASSWORD_WIFI_ANDA"; // Ganti dengan password Wi-Fi Anda

// Broker MQTT (Default menggunakan broker publik gratis HiveMQ)
const char* mqtt_server = "broker.hivemq.com";
const int mqtt_port = 1883;
const char* mqtt_user = "";     // Kosongkan jika broker publik tidak butuh auth
const char* mqtt_pass = "";     // Kosongkan jika broker publik tidak butuh auth

// Topic MQTT
const char* topic_tap = "taptrack/pintu/tap";
const char* topic_command = "taptrack/pintu/command";

// --- DEFINISI PIN ---
// RFID 1 (Masuk)
#define SS_1_PIN    5
#define RST_1_PIN   14

// RFID 2 (Keluar)
#define SS_2_PIN    32
#define RST_2_PIN   33

// Relay
#define RELAY_PIN   25

// --- INISIALISASI OBJEK ---
MFRC522 mfrc522_1(SS_1_PIN, RST_1_PIN);
MFRC522 mfrc522_2(SS_2_PIN, RST_2_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2); // Alamat I2C umum: 0x27 atau 0x3F

WiFiClient espClient;
PubSubClient mqttClient(espClient);

// --- GLOBAL VARIABLES ---
unsigned long lastMqttReconnectAttempt = 0;
bool gateUnlocked = false; // Status apakah gerbang dibuka bebas dari dashboard

void setup() {
  Serial.begin(115200);
  
  // Inisialisasi SPI Bus
  SPI.begin();
  
  // Inisialisasi RFID Readers
  mfrc522_1.PCD_Init();
  mfrc522_2.PCD_Init();
  
  // Inisialisasi Relay Pintu
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW); // Default: Mati/Terkunci

  // Inisialisasi LCD
  lcd.init();
  lcd.backlight();
  
  tampilkanStandby();

  // Memulai Koneksi Wi-Fi
  setup_wifi();

  // Setup MQTT
  mqttClient.setServer(mqtt_server, mqtt_port);
  mqttClient.setCallback(mqttCallback);
}

void loop() {
  // Cek Status Wi-Fi dan MQTT secara non-blocking
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

  // Cek Reader 1 (MASUK) jika gerbang tidak dalam kondisi Buka Bebas (Unlocked)
  if (!gateUnlocked) {
    if (mfrc522_1.PICC_IsNewCardPresent() && mfrc522_1.PICC_ReadCardSerial()) {
      handleRfidScan(&mfrc522_1, "masuk");
    }
    
    // Cek Reader 2 (KELUAR)
    if (mfrc522_2.PICC_IsNewCardPresent() && mfrc522_2.PICC_ReadCardSerial()) {
      handleRfidScan(&mfrc522_2, "keluar");
    }
  }
}

// --- FUNGSI WIFI ---
void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Menghubungkan ke ");
  Serial.println(ssid);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MENYAMBUNG WIFI ");
  
  WiFi.begin(ssid, password);

  int counter = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    lcd.setCursor(counter % 16, 1);
    lcd.print(".");
    counter++;
    if (counter > 30) { // Timeout wifi, lanjutkan loop agar ESP32 tidak hang
      break;
    }
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("");
    Serial.println("Wi-Fi terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WIFI TERHUBUNG! ");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    delay(1500);
    tampilkanStandby();
  } else {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WIFI OFFLINE    ");
    delay(1500);
    tampilkanStandby();
  }
}

// --- FUNGSI MQTT ---
bool reconnectMqtt() {
  Serial.print("Mencoba koneksi MQTT...");
  String clientId = "ESP32Client-" + String(random(0xffff), HEX);
  
  if (mqttClient.connect(clientId.c_str(), mqtt_user, mqtt_pass)) {
    Serial.println("terhubung!");
    // Subscribe ke topic command untuk menerima status dari Laravel
    mqttClient.subscribe(topic_command);
    return true;
  } else {
    Serial.print("gagal, rc=");
    Serial.print(mqttClient.state());
    Serial.println(" coba lagi dalam 5 detik.");
    return false;
  }
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Pesan masuk di topic [");
  Serial.print(topic);
  Serial.print("]: ");
  
  String msg = "";
  for (int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }
  Serial.println(msg);

  // Parsing JSON Payload
  StaticJsonDocument<256> doc;
  DeserializationError error = deserializeJson(doc, msg);

  if (error) {
    Serial.print("Gagal parsing JSON: ");
    Serial.println(error.c_str());
    return;
  }

  String status = doc["status"] | "";
  String message = doc["message"] | "";

  // 1. Logika Akses Diterima (Success)
  if (status == "success") {
    String name = doc["name"] | "KARYAWAN";
    String reader = doc["reader"] | "masuk";
    
    Serial.println("🔑 AKSES DITERIMA untuk " + name);
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("AKSES DITERIMA !");
    
    // Potong nama agar muat di LCD 16 Karakter
    if (name.length() > 16) {
      name = name.substring(0, 16);
    }
    lcd.setCursor(0, 1);
    lcd.print(name);

    // Aktifkan Relay Pintu
    digitalWrite(RELAY_PIN, HIGH);
    
    // Biarkan pintu terbuka 3 detik (jika tidak dalam mode bebas)
    if (!gateUnlocked) {
      delay(3000);
      digitalWrite(RELAY_PIN, LOW); // Kunci kembali
    }

    // Refresh sensor RFID & LCD pasca noise relay
    refreshDevices();
    tampilkanStandby();
  }
  
  // 2. Logika Akses Ditolak (Error)
  else if (status == "error") {
    String uid = doc["uid"] | "UNKNOWN";
    Serial.println("❌ AKSES DITOLAK untuk UID: " + uid);
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(" ACCESS DENIED! ");
    lcd.setCursor(0, 1);
    lcd.print(uid);

    delay(2500);
    tampilkanStandby();
  }
  
  // 3. Logika Kendali Manual dari Dashboard (Control)
  else if (status == "control") {
    String action = doc["action"] | "";
    if (action == "unlock") {
      gateUnlocked = true;
      digitalWrite(RELAY_PIN, HIGH); // Buka pintu terus menerus
      Serial.println("🔓 GERBANG DISET BEBAS (UNLOCKED)");
      tampilkanStandby();
    } else if (action == "lock") {
      gateUnlocked = false;
      digitalWrite(RELAY_PIN, LOW); // Kunci kembali pintu
      Serial.println("🔒 GERBANG DISET KUNCI (LOCKED)");
      tampilkanStandby();
    }
  }
}

// --- FUNGSI SCAN RFID ---
void handleRfidScan(MFRC522* rfid, String readerName) {
  // Membaca UID
  String strUID = "";
  for (byte i = 0; i < rfid->uid.size; i++) {
    strUID += String(rfid->uid.uidByte[i] < 0x10 ? "0" : "");
    strUID += String(rfid->uid.uidByte[i], HEX);
    if (i < rfid->uid.size - 1) strUID += " ";
  }
  strUID.toUpperCase();

  Serial.println("RFID Tapped di reader: " + readerName + " | UID: " + strUID);

  // Tampilkan status "Mengecek Kartu..." di LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  if (readerName == "masuk") {
    lcd.print("   RFID MASUK   ");
  } else {
    lcd.print("  RFID KELUAR   ");
  }
  lcd.setCursor(0, 1);
  lcd.print("MENGECEK KARTU...");

  // Kirim data ke Laravel via MQTT
  if (mqttClient.connected()) {
    StaticJsonDocument<128> doc;
    doc["uid"] = strUID;
    doc["reader"] = readerName;

    char buffer[128];
    serializeJson(doc, buffer);
    
    mqttClient.publish(topic_tap, buffer);
    Serial.println("Mengirim data tap ke MQTT...");
  } else {
    Serial.println("MQTT Offline. Tidak bisa memverifikasi kartu.");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("SISTEM OFFLINE  ");
    lcd.setCursor(0, 1);
    lcd.print("HUBUNGI ADMIN   ");
    delay(2000);
    tampilkanStandby();
  }

  // Hentikan pembacaan kartu saat ini
  rfid->PICC_HaltA();
  rfid->PCD_StopCrypto1();
}

// --- TAMPILAN STANDBY LCD ---
void tampilkanStandby() {
  lcd.clear();
  if (gateUnlocked) {
    lcd.setCursor(0, 0);
    lcd.print("  ROLLING DOOR  ");
    lcd.setCursor(0, 1);
    lcd.print("  AKSES BEBAS  ");
  } else {
    lcd.setCursor(0, 0);
    lcd.print("  SISTEM AKTIF  ");
    lcd.setCursor(0, 1);
    lcd.print(" TEMPELKAN KARTU");
  }
}

// --- REFRESH/RE-INIT ALAT ---
void refreshDevices() {
  delay(100);
  lcd.init();
  lcd.backlight();
  mfrc522_1.PCD_Init();
  mfrc522_2.PCD_Init();
  Serial.println("🔄 RFID dan LCD berhasil di-refresh.");
}
