/*
 * =============================================================================
 * FIRMWARE AKSES PINTU - ESP32 #1 (MASUK)
 * =============================================================================
 * Deskripsi:
 *   ESP32 ini dipasang di sisi DALAM / LUAR ruangan sebagai pintu MASUK.
 *   Membaca RFID -> mengirim UID + direction "masuk" ke broker MQTT ->
 *   Laravel memvalidasi -> mengirim balasan command ke ESP32 -> buka relay.
 *
 * Hardware: ESP32 DevKit v1 (30-Pin)
 *
 * RFID RC522:
 *   - SDA (SS) -> GPIO 5
 *   - RST      -> GPIO 14
 *   - SCK      -> GPIO 18 (SPI default)
 *   - MOSI     -> GPIO 23 (SPI default)
 *   - MISO     -> GPIO 19 (SPI default)
 *   - 3.3V     -> 3V3
 *   - GND      -> GND
 *
 * LCD I2C (16x2):
 *   - SDA      -> GPIO 21
 *   - SCL      -> GPIO 22
 *   - VCC      -> 5V
 *   - GND      -> GND
 *
 * Solenoid/Relay (1-Channel):
 *   - IN       -> GPIO 25
 *   - VCC      -> 5V (atau VIN)
 *   - GND      -> GND
 *
 * MQTT Topics:
 *   - Publish  : taptrack/akses_pintu/tap      (kirim UID)
 *   - Subscribe: taptrack/akses_pintu/command  (terima balasan Laravel)
 *
 * Libraries (PlatformIO lib_deps):
 *   - miguelbalboa/MFRC522@^1.4.11
 *   - marcoschwartz/LiquidCrystal_I2C@^1.1.4
 *   - knolleary/PubSubClient@^2.8
 *   - bblanchon/ArduinoJson@^6.21.3
 * =============================================================================
 */

#include <Arduino.h>
#include <WiFi.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// =============================================================================
// KONFIGURASI — WAJIB DIGANTI SESUAI ENVIRONMENT ANDA
// =============================================================================
const char* WIFI_SSID     = "taptrack";
const char* WIFI_PASS     = "apayaaaa";
const char* MQTT_SERVER   = "10.39.197.137";  // IP broker MQTT (sesuai .env MQTT_HOST)
const int   MQTT_PORT     = 1883;
const char* MQTT_USER     = "";               // Kosongkan jika tidak pakai auth
const char* MQTT_PASS_STR = "";               // Kosongkan jika tidak pakai auth

// ID unik untuk ESP ini (bedakan dengan ESP Keluar!)
const char* DEVICE_ID     = "ESP32-PINTU-MASUK";

// Topic MQTT
const char* TOPIC_TAP     = "taptrack/akses_pintu/tap";
const char* TOPIC_COMMAND = "taptrack/akses_pintu/command";

// Arah / Direction (JANGAN DIUBAH untuk file ini)
const char* DIRECTION     = "masuk";

// =============================================================================
// DEFINISI PIN
// =============================================================================
#define SS_PIN    5
#define RST_PIN   14
#define RELAY_PIN 25

// SPI pins eksplisit (VSPI default ESP32)
#define SPI_SCK   18
#define SPI_MISO  19
#define SPI_MOSI  23

// =============================================================================
// INISIALISASI OBJEK
// =============================================================================
MFRC522           rfid(SS_PIN, RST_PIN);
LiquidCrystal_I2C lcd(0x27, 16, 2);

WiFiClient    espClient;
PubSubClient  mqttClient(espClient);

// =============================================================================
// VARIABEL GLOBAL
// =============================================================================
unsigned long lastMqttReconnectAttempt = 0;
bool          gateUnlocked             = false;

// =============================================================================
// DEKLARASI FUNGSI
// =============================================================================
void setupWifi();
bool reconnectMqtt();
void mqttCallback(char* topic, byte* payload, unsigned int length);
void handleRfidScan();
void bukaRelay(int detik);
void tampilkanStandby();
void refreshDevices();

// =============================================================================
// SETUP
// =============================================================================
void setup() {
  Serial.begin(115200);
  Serial.println("\n===================================");
  Serial.println("  PINTU MASUK - SISTEM RFID AKTIF  ");
  Serial.println("===================================");

  // SPI + RFID — gunakan pin eksplisit agar tidak konflik dengan WiFi internal
  SPI.begin(SPI_SCK, SPI_MISO, SPI_MOSI, SS_PIN);
  rfid.PCD_Init();
  delay(50);

  // Relay
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW); // Terkunci saat booting

  // LCD
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("  PINTU MASUK   ");
  lcd.setCursor(0, 1);
  lcd.print("  MEMUAT...     ");
  delay(800);

  // WiFi + MQTT
  setupWifi();
  mqttClient.setServer(MQTT_SERVER, MQTT_PORT);
  mqttClient.setCallback(mqttCallback);

  // PENTING: Re-init RFID setelah WiFi selesai
  // WiFi aktif kadang bikin RFID crash - cukup PCD_Init ulang tanpa SPI.end()
  delay(200);
  rfid.PCD_Init();
  rfid.PCD_SetAntennaGain(rfid.RxGain_max); // Set gain maksimum
  delay(50);
  Serial.println("[RFID] PCD_Init ulang pasca WiFi setup selesai.");
  byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
  Serial.printf("[RFID] Firmware version: 0x%02X %s\n", v,
    (v == 0x91) ? "= v1.0" :
    (v == 0x92) ? "= v2.0" :
    (v == 0x00 || v == 0xFF) ? "= TIDAK TERDETEKSI! Cek wiring/power." : "= Unknown");
}

// =============================================================================
// LOOP UTAMA
// =============================================================================
void loop() {
  // Reconnect MQTT non-blocking (coba setiap 5 detik jika putus)
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

  // Baca RFID hanya jika gerbang tidak dalam mode "bebas/unlocked"
  if (!gateUnlocked) {
    // Debug + auto-recovery: log setiap 3 detik
    static unsigned long lastRfidDebug = 0;
    if (millis() - lastRfidDebug > 3000) {
      lastRfidDebug = millis();
      byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
      Serial.printf("[RFID] Status: 0x%02X | Menunggu kartu...\n", v);

      // Auto-recovery: jika RFID tidak terdeteksi, re-init tanpa SPI.end()
      if (v == 0xFF || v == 0x00) {
        Serial.println("[RFID] ⚠️ Tidak terdeteksi! Re-init RFID...");
        rfid.PCD_Init();
        rfid.PCD_SetAntennaGain(rfid.RxGain_max);
        delay(50);
      }
    }

    if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
      handleRfidScan();
    }
  }
}

// =============================================================================
// SETUP WIFI
// =============================================================================
void setupWifi() {
  Serial.printf("Menghubungkan ke WiFi: %s\n", WIFI_SSID);

  // Pastikan mode STA dan reset koneksi lama
  WiFi.disconnect(true);
  delay(200);
  WiFi.mode(WIFI_STA);
  delay(100);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("MENYAMBUNG WIFI ");
  lcd.setCursor(0, 1);
  lcd.print("                ");

  WiFi.begin(WIFI_SSID, WIFI_PASS);

  // Timeout 30 detik (60 x 500ms)
  int counter = 0;
  const int MAX_ATTEMPTS = 60;
  while (WiFi.status() != WL_CONNECTED && counter < MAX_ATTEMPTS) {
    delay(500);
    Serial.print(".");
    // Tampilkan sisa waktu di LCD baris 2
    int sisaDetik = (MAX_ATTEMPTS - counter) / 2;
    lcd.setCursor(0, 1);
    lcd.print("Tunggu: ");
    lcd.print(sisaDetik);
    lcd.print("s   ");
    counter++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\nWiFi terhubung! IP: %s\n", WiFi.localIP().toString().c_str());
    
    // Kurangi TX power WiFi agar arus tidak drop 3.3V rail RFID
    // WIFI_POWER_8_5dBm = ~25mA, default 20dBm = ~250mA peak
    WiFi.setTxPower(WIFI_POWER_8_5dBm);
    Serial.println("[WiFi] TX power diturunkan untuk stabilkan daya RFID.");
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WIFI TERHUBUNG! ");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP().toString());
    delay(1500);
  } else {
    Serial.printf("\nWiFi GAGAL setelah %d detik. Cek SSID/password/frekuensi.\n", MAX_ATTEMPTS / 2);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WIFI OFFLINE    ");
    lcd.setCursor(0, 1);
    lcd.print("CEK KONEKSI...  ");
    delay(1500);
  }

  tampilkanStandby();
}

// =============================================================================
// RECONNECT MQTT
// =============================================================================
bool reconnectMqtt() {
  Serial.print("Mencoba koneksi MQTT...");
  String clientId = String(DEVICE_ID) + "-" + String(random(0xffff), HEX);

  bool connected;
  if (strlen(MQTT_USER) > 0) {
    connected = mqttClient.connect(clientId.c_str(), MQTT_USER, MQTT_PASS_STR);
  } else {
    connected = mqttClient.connect(clientId.c_str());
  }

  if (connected) {
    Serial.println("terhubung!");
    mqttClient.subscribe(TOPIC_COMMAND);
    Serial.printf("Subscribe ke topic: %s\n", TOPIC_COMMAND);
    return true;
  } else {
    Serial.printf("gagal, rc=%d — coba lagi 5 detik.\n", mqttClient.state());
    return false;
  }
}

// =============================================================================
// CALLBACK MQTT (MENERIMA BALASAN DARI LARAVEL)
// =============================================================================
void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String msg = "";
  for (unsigned int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }
  Serial.printf("[MQTT IN] Topic: %s | Payload: %s\n", topic, msg.c_str());

  StaticJsonDocument<256> doc;
  DeserializationError error = deserializeJson(doc, msg);
  if (error) {
    Serial.printf("JSON parsing gagal: %s\n", error.c_str());
    return;
  }

  String status    = doc["status"]    | "";
  String direction = doc["direction"] | "";

  // Hanya proses command yang ditujukan untuk "masuk" atau broadcast (tanpa direction)
  if (!direction.isEmpty() && direction != DIRECTION) {
    Serial.printf("Command untuk arah '%s', diabaikan (device ini: '%s').\n",
                  direction.c_str(), DIRECTION);
    return;
  }

  // ----- 1. AKSES DITERIMA -----
  if (status == "success") {
    String name = doc["name"] | "KARYAWAN";
    Serial.printf("✅ AKSES DITERIMA | Nama: %s\n", name.c_str());

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("AKSES DITERIMA !");
    if (name.length() > 16) name = name.substring(0, 16);
    lcd.setCursor(0, 1);
    lcd.print(name);

    bukaRelay(3); // Buka relay selama 3 detik
    refreshDevices();
    tampilkanStandby();
  }

  // ----- 2. AKSES DITOLAK -----
  else if (status == "error") {
    String uid = doc["uid"] | "UNKNOWN";
    Serial.printf("❌ AKSES DITOLAK | UID: %s\n", uid.c_str());

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(" ACCESS DENIED! ");
    lcd.setCursor(0, 1);
    // Tampilkan UID yang ditolak
    if (uid.length() > 16) uid = uid.substring(0, 16);
    lcd.print(uid);

    delay(2500);
    tampilkanStandby();
  }

  // ----- 3. KONTROL MANUAL DARI DASHBOARD -----
  else if (status == "control") {
    String action = doc["action"] | "";
    if (action == "unlock") {
      gateUnlocked = true;
      digitalWrite(RELAY_PIN, HIGH);
      Serial.println("🔓 RELAY: MODE BEBAS (UNLOCKED)");
    } else if (action == "lock") {
      gateUnlocked = false;
      digitalWrite(RELAY_PIN, LOW);
      Serial.println("🔒 RELAY: MODE KUNCI (LOCKED)");
    }
    tampilkanStandby();
  }
}

// =============================================================================
// HANDLE SCAN RFID
// =============================================================================
void handleRfidScan() {
  // Baca UID
  String strUID = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    strUID += String(rfid.uid.uidByte[i] < 0x10 ? "0" : "");
    strUID += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) strUID += " ";
  }
  strUID.toUpperCase();

  Serial.printf("[RFID] UID Terdeteksi: %s | Direction: %s\n", strUID.c_str(), DIRECTION);

  // Tampilkan "mengecek" di LCD
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("  PINTU MASUK   ");
  lcd.setCursor(0, 1);
  lcd.print("MENGECEK KARTU..");

  // Publish ke MQTT jika terhubung
  if (mqttClient.connected()) {
    StaticJsonDocument<128> doc;
    doc["uid"]       = strUID;
    doc["direction"] = DIRECTION;
    doc["device_id"] = DEVICE_ID;

    char buffer[128];
    serializeJson(doc, buffer);
    mqttClient.publish(TOPIC_TAP, buffer);
    Serial.printf("[MQTT OUT] %s\n", buffer);
  } else {
    Serial.println("[RFID] MQTT Offline — tidak bisa verifikasi.");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("SISTEM OFFLINE  ");
    lcd.setCursor(0, 1);
    lcd.print("HUBUNGI ADMIN   ");
    delay(2000);
    tampilkanStandby();
  }

  // Hentikan pembacaan kartu saat ini
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}

// =============================================================================
// BUKA RELAY (BERI DURASI DALAM DETIK)
// =============================================================================
void bukaRelay(int detik) {
  if (gateUnlocked) return; // Sudah terbuka bebas, tidak perlu timer

  Serial.printf("[RELAY] Membuka selama %d detik...\n", detik);
  digitalWrite(RELAY_PIN, HIGH);
  delay(detik * 1000UL);
  digitalWrite(RELAY_PIN, LOW);
  Serial.println("[RELAY] Terkunci kembali.");
}

// =============================================================================
// TAMPILAN STANDBY LCD
// =============================================================================
void tampilkanStandby() {
  lcd.clear();
  if (gateUnlocked) {
    lcd.setCursor(0, 0);
    lcd.print("  PINTU MASUK   ");
    lcd.setCursor(0, 1);
    lcd.print("  AKSES BEBAS   ");
  } else {
    lcd.setCursor(0, 0);
    lcd.print("  PINTU MASUK   ");
    lcd.setCursor(0, 1);
    lcd.print(" TEMPELKAN KARTU");
  }
}

// =============================================================================
// REFRESH/RE-INIT PERANGKAT (atasi noise pasca-relay)
// =============================================================================
void refreshDevices() {
  delay(100);
  lcd.init();
  lcd.backlight();
  rfid.PCD_Init();
  Serial.println("[SYS] RFID dan LCD berhasil di-refresh.");
}
