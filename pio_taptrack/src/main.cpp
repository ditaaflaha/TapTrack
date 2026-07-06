/*
 * UNIFIED SOURCE FILE (src/main.cpp) - PlatformIO Project
 * ----------------------------------------------------
 * Conditional compilation based on -D build flags defined in platformio.ini:
 * - NODE_ABSENSI     : Titik 1 (Dual RFID readers: SS=5/RST=2, SS=32/RST=33)
 * - NODE_AKSES_PINTU : Titik 2 (Single RFID reader: SS=5/RST=2, 2-Channel Relay: 25/26)
 * - NODE_KANTIN      : Titik 3 (Single RFID reader: SS=5/RST=4)
 */

#include <Arduino.h>
#include <WiFi.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include "config.h"

// --- OBJECT INITIALIZATION ---
#if defined(RFID_DUAL_READER)
    MFRC522 mfrc522_1(SS_1_PIN, RST_1_PIN);
    MFRC522 mfrc522_2(SS_2_PIN, RST_2_PIN);
#elif defined(RFID_SINGLE_READER)
    MFRC522 mfrc522(SS_PIN, RST_PIN);
#endif

LiquidCrystal_I2C lcd(0x27, 16, 2);
WiFiClient espClient;
PubSubClient mqttClient(espClient);

// --- GLOBAL VARIABLES ---
unsigned long lastMqttReconnectAttempt = 0;
bool gateUnlocked = false; // Only used if HAS_RELAY is defined

// --- FUNCTION DECLARATIONS ---
void setup_wifi();
bool reconnectMqtt();
void mqttCallback(char* topic, byte* payload, unsigned int length);
#if defined(RFID_DUAL_READER)
void handleRfidScan(MFRC522* rfid, String readerName);
#elif defined(RFID_SINGLE_READER)
void handleRfidScan(MFRC522* rfid, String readerName);
#endif
void tampilkanStandby();
void refreshDevices();

void setup() {
    Serial.begin(115200);
    delay(500);
    Serial.printf("\n--- BOOTING %s NODE ---\n", NODE_TYPE_NAME);
    
    // SPI Bus Initialization
    SPI.begin();
    
    // RFID Initialization
    #if defined(RFID_DUAL_READER)
        mfrc522_1.PCD_Init();
        mfrc522_2.PCD_Init();
        Serial.println("Dual RFID Readers Initialized.");
    #elif defined(RFID_SINGLE_READER)
        mfrc522.PCD_Init();
        Serial.println("Single RFID Reader Initialized.");
    #endif

    // Relay Initialization (Only for Akses Pintu)
    #if defined(HAS_RELAY)
        pinMode(RELAY_1_PIN, OUTPUT);
        pinMode(RELAY_2_PIN, OUTPUT);
        digitalWrite(RELAY_1_PIN, LOW); // Terkunci
        digitalWrite(RELAY_2_PIN, LOW); // Terkunci
        Serial.println("Relay Pins 25 & 26 Initialized.");
    #endif

    // LCD Initialization
    lcd.init();
    lcd.backlight();
    tampilkanStandby();

    // Wifi Connection & MQTT
    setup_wifi();
    mqttClient.setServer(MQTT_SERVER, MQTT_PORT);
    mqttClient.setCallback(mqttCallback);
}

void loop() {
    // Non-blocking MQTT Reconnection
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

    // Scan RFID Cards
    #if defined(RFID_DUAL_READER)
        if (!gateUnlocked) {
            if (mfrc522_1.PICC_IsNewCardPresent() && mfrc522_1.PICC_ReadCardSerial()) {
                handleRfidScan(&mfrc522_1, "masuk");
            }
            if (mfrc522_2.PICC_IsNewCardPresent() && mfrc522_2.PICC_ReadCardSerial()) {
                handleRfidScan(&mfrc522_2, "keluar");
            }
        }
    #elif defined(RFID_SINGLE_READER)
        #if defined(HAS_RELAY)
            if (!gateUnlocked) {
                if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
                    handleRfidScan(&mfrc522, "akses");
                }
            }
        #else
            if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
                handleRfidScan(&mfrc522, "kantin");
            }
        #endif
    #endif
}

void setup_wifi() {
    Serial.printf("Connecting to Wi-Fi: %s\n", WIFI_SSID);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("MENYAMBUNG WIFI ");

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    int counter = 0;
    while (WiFi.status() != WL_CONNECTED && counter < 20) {
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
        Serial.println("\nWi-Fi connection timeout.");
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("WIFI OFFLINE    ");
        delay(1500);
    }
    tampilkanStandby();
}

bool reconnectMqtt() {
    Serial.print("Connecting to MQTT broker...");
    String clientId = "ESP32Client-" + String(NODE_TYPE_NAME) + "-" + String(random(0xffff), HEX);
    
    bool connected = false;
    if (String(MQTT_USER).length() > 0) {
        connected = mqttClient.connect(clientId.c_str(), MQTT_USER, MQTT_PASS);
    } else {
        connected = mqttClient.connect(clientId.c_str());
    }

    if (connected) {
        Serial.println("connected!");
        mqttClient.subscribe(MQTT_TOPIC_COMMAND);
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
    Serial.printf("MQTT Incoming [%s]: %s\n", topic, msg.c_str());

    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, msg);
    if (error) {
        Serial.println("JSON Deserialization failed.");
        return;
    }

    String status = doc["status"] | "";

    // 1. Logika Validasi Sukses (Success Response)
    if (status == "success") {
        String name = doc["name"] | "KARYAWAN";
        
        #if defined(NODE_ABSENSI)
            String reader = doc["reader"] | "masuk";
            lcd.clear();
            lcd.setCursor(0, 0);
            if (reader == "masuk") {
                lcd.print(" TAP IN BERHASIL");
            } else {
                lcd.print("TAP OUT BERHASIL");
            }
            if (name.length() > 16) name = name.substring(0, 16);
            lcd.setCursor(0, 1);
            lcd.print(name);
            delay(3000);
            tampilkanStandby();

        #elif defined(NODE_AKSES_PINTU)
            int relayCh = doc["relay_ch"] | 1;
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("AKSES DITERIMA !");
            if (name.length() > 16) name = name.substring(0, 16);
            lcd.setCursor(0, 1);
            lcd.print(name);

            // Nyalakan Solenoid yang sesuai (1 or 2)
            int targetPin = (relayCh == 2) ? RELAY_2_PIN : RELAY_1_PIN;
            digitalWrite(targetPin, HIGH);
            
            if (!gateUnlocked) {
                delay(3000);
                digitalWrite(targetPin, LOW); // Kunci kembali
            }
            refreshDevices();
            tampilkanStandby();

        #elif defined(NODE_KANTIN)
            String info = doc["info"] | "Sukses";
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("TRANSAKSI SUKSES");
            if (info.length() > 16) info = info.substring(0, 16);
            lcd.setCursor(0, 1);
            lcd.print(info);
            delay(3000);
            tampilkanStandby();
        #endif
    }
    
    // 2. Logika Validasi Gagal (Access Denied)
    else if (status == "error") {
        String msg = doc["message"] | "invalid";
        String name = doc["name"] | "";
        String uid = doc["uid"] | "UNKNOWN";
        
        lcd.clear();
        lcd.setCursor(0, 0);

        #if defined(NODE_ABSENSI)
            if (msg == "already_in") {
                lcd.print("SUDAH ABSEN IN! ");
                if (name.length() > 16) name = name.substring(0, 16);
                lcd.setCursor(0, 1);
                lcd.print(name);
            } else if (msg == "already_out") {
                lcd.print("SUDAH ABSEN OUT!");
                if (name.length() > 16) name = name.substring(0, 16);
                lcd.setCursor(0, 1);
                lcd.print(name);
            } else if (msg == "not_in_yet") {
                lcd.print("BELUM ABSEN IN! ");
                if (name.length() > 16) name = name.substring(0, 16);
                lcd.setCursor(0, 1);
                lcd.print(name);
            } else {
                lcd.print(" ACCESS DENIED! ");
                lcd.setCursor(0, 1);
                lcd.print(uid);
            }
        #elif defined(NODE_KANTIN)
            lcd.print(" KARTU ASING!  ");
            lcd.setCursor(0, 1);
            lcd.print(uid);
        #else
            lcd.print(" ACCESS DENIED! ");
            lcd.setCursor(0, 1);
            lcd.print(uid);
        #endif

        delay(2500);
        tampilkanStandby();
    }
    
    // 3. Logika Kendali Manual (Control from Dashboard)
    else if (status == "control") {
        #if defined(HAS_RELAY)
            String action = doc["action"] | "";
            if (action == "unlock") {
                gateUnlocked = true;
                digitalWrite(RELAY_1_PIN, HIGH);
                digitalWrite(RELAY_2_PIN, HIGH);
                Serial.println("🔓 Gate state set to FREE ACCESS.");
            } else if (action == "lock") {
                gateUnlocked = false;
                digitalWrite(RELAY_1_PIN, LOW);
                digitalWrite(RELAY_2_PIN, LOW);
                Serial.println("🔒 Gate state set to LOCKED.");
            }
            tampilkanStandby();
        #endif
    }
}

#if defined(RFID_DUAL_READER)
void handleRfidScan(MFRC522* rfid, String readerName) {
    String strUID = "";
    for (byte i = 0; i < rfid->uid.size; i++) {
        strUID += String(rfid->uid.uidByte[i] < 0x10 ? "0" : "");
        strUID += String(rfid->uid.uidByte[i], HEX);
        if (i < rfid->uid.size - 1) strUID += " ";
    }
    strUID.toUpperCase();

    Serial.printf("[%s] Scanned Card UID: %s\n", readerName.c_str(), strUID.c_str());

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(readerName == "masuk" ? "   RFID MASUK   " : "  RFID KELUAR   ");
    lcd.setCursor(0, 1);
    lcd.print("MENGECEK KARTU...");

    if (mqttClient.connected()) {
        StaticJsonDocument<128> doc;
        doc["uid"] = strUID;
        doc["reader"] = readerName;

        char buffer[128];
        serializeJson(doc, buffer);
        mqttClient.publish(MQTT_TOPIC_TAP, buffer);
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

#elif defined(RFID_SINGLE_READER)
void handleRfidScan(MFRC522* rfid, String readerName) {
    String strUID = "";
    for (byte i = 0; i < rfid->uid.size; i++) {
        strUID += String(rfid->uid.uidByte[i] < 0x10 ? "0" : "");
        strUID += String(rfid->uid.uidByte[i], HEX);
        if (i < rfid->uid.size - 1) strUID += " ";
    }
    strUID.toUpperCase();

    Serial.printf("[%s] Scanned Card UID: %s\n", readerName.c_str(), strUID.c_str());

    lcd.clear();
    lcd.setCursor(0, 0);
    #if defined(NODE_KANTIN)
        lcd.print("   TAP KANTIN   ");
    #else
        lcd.print("  AKSES GERBANG  ");
    #endif
    lcd.setCursor(0, 1);
    lcd.print("MENGECEK KARTU...");

    if (mqttClient.connected()) {
        StaticJsonDocument<128> doc;
        doc["uid"] = strUID;

        char buffer[128];
        serializeJson(doc, buffer);
        mqttClient.publish(MQTT_TOPIC_TAP, buffer);
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
#endif

void tampilkanStandby() {
    lcd.clear();
    #if defined(NODE_ABSENSI)
        lcd.setCursor(0, 0);
        lcd.print("  SISTEM ABSEN  ");
        lcd.setCursor(0, 1);
        lcd.print(" TEMPELKAN KARTU");

    #elif defined(NODE_AKSES_PINTU)
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

    #elif defined(NODE_KANTIN)
        lcd.setCursor(0, 0);
        lcd.print("   TAP KANTIN   ");
        lcd.setCursor(0, 1);
        lcd.print("Rp 12.000 / TAP ");
    #endif
}

void refreshDevices() {
    delay(100);
    lcd.init();
    lcd.backlight();
    #if defined(RFID_DUAL_READER)
        mfrc522_1.PCD_Init();
        mfrc522_2.PCD_Init();
    #elif defined(RFID_SINGLE_READER)
        mfrc522.PCD_Init();
    #endif
}
