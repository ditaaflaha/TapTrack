#ifndef CONFIG_H
#define CONFIG_H

// ====================================================
// SHARED CONFIGURATIONS (Change these for your network)
// ====================================================
#define WIFI_SSID           "taptrack"         // Ganti dengan SSID Wi-Fi Anda
#define WIFI_PASSWORD       "apayaaaa"     // Ganti dengan password Wi-Fi Anda

#define MQTT_SERVER         "10.39.197.137"
#define MQTT_PORT           1883
#define MQTT_USER           ""                       // Ganti jika broker membutuhkan username
#define MQTT_PASS           ""                       // Ganti jika broker membutuhkan password

// ====================================================
// CONDITIONALLY COMPILED CONFIGURATIONS PER ENVIRONMENT
// ====================================================

#if defined(NODE_ABSENSI)
    #define NODE_TYPE_NAME      "ABSENSI"
    #define MQTT_TOPIC_TAP      "taptrack/absensi/tap"
    #define MQTT_TOPIC_COMMAND  "taptrack/absensi/command"
    
    // Feature flag
    #define RFID_DUAL_READER
    
    // Pinout configurations for dual readers
    #define SS_1_PIN            5
    #define RST_1_PIN           2
    #define SS_2_PIN            32
    #define RST_2_PIN           33

#elif defined(NODE_AKSES_PINTU)
    #define NODE_TYPE_NAME      "AKSES PINTU"
    #define MQTT_TOPIC_TAP      "taptrack/akses_pintu/tap"
    #define MQTT_TOPIC_COMMAND  "taptrack/akses_pintu/command"
    
    // Feature flags
    #define RFID_SINGLE_READER
    #define HAS_RELAY
    
    // Pinout configurations
    #define SS_PIN              5
    #define RST_PIN             2
    #define RELAY_1_PIN         25  // IN1 (Solenoid 1 - Masuk)
    #define RELAY_2_PIN         26  // IN2 (Solenoid 2 - Keluar)

#elif defined(NODE_KANTIN)
    #define NODE_TYPE_NAME      "KANTIN"
    #define MQTT_TOPIC_TAP      "taptrack/kantin/tap"
    #define MQTT_TOPIC_COMMAND  "taptrack/kantin/command"
    
    // Feature flag
    #define RFID_SINGLE_READER
    
    // Pinout configurations
    #define SS_PIN              5
    #define RST_PIN             4   // Kantin RST on GPIO 4

#else
    #error "Please select a valid build environment in PlatformIO (absensi, akses_pintu, or kantin)!"
#endif

#endif // CONFIG_H
