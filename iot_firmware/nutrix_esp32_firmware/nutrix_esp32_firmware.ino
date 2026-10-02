/**
 * ==============================================================================
 * SISTEM IoT NUTRIX — SMART AGRICULTURE WIRELESS CONTROLLER (ESP32)
 * ==============================================================================
 * Multi-Sensor Scientific Architecture (Scopus-Grade Precision):
 * - Sensor 1: Capacitive Soil Moisture Sensor V2.0 on GPIO 34 (ADC1_CH6)
 * - Sensor 2: Resistive Soil Moisture Sensor HD-38 on GPIO 35 (ADC1_CH7)
 * - Signal Processing: Trimmed-Mean Filter (20 samples, drop top 4 & bottom 4)
 * - Calibrated 2-Point Linear Transfer Function (ADC to VWC %)
 * - Network Identity: Hostname "Kelompok Nutrix" (mDNS & DHCP)
 * - Zero Ghost Data Architecture: Authenticated Cloud Transmission
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiManager.h>      // Library WiFiManager oleh tzapu
#include <ArduinoJson.h>       // Library ArduinoJson oleh Benoit Blanchon
#include <Preferences.h>

// ── 1. DEFINISI PIN SENSOR & AKTUATOR (Shield G-V-S Ready) ─────────────────
#define PIN_CAPACITIVE   34     // Sensor Capacitive V2.0 (ADC1_CH6) -> Baris D34 [S]
#define PIN_RESISTIVE    35     // Sensor Resistive HD-38 (ADC1_CH7) -> Baris D35 [S]
#define PIN_RELAY        26     // Relay Pompa / Solenoid Valve (Opsional)
#define PIN_BUZZER       27     // Buzzer Indikator
#define PIN_LED_STATUS   2      // Onboard LED ESP32

// ── 2. KALIBRASI ADC SENSOR ILMIAH (2-Point Calibration) ───────────────────
// Sensor Capacitive V2.0 (Kering di Udara = Tinggi, Basah di Air = Rendah)
const int CAP_ADC_DRY   = 3200; // Kondisi kering di udara (0% VWC)
const int CAP_ADC_WET   = 1450; // Kondisi jenuh di air (100% VWC)

// Sensor Resistive HD-38 (Kering di Udara = Tinggi, Basah di Air = Rendah)
const int RES_ADC_DRY   = 3500; // Kondisi kering di udara (0% VWC)
const int RES_ADC_WET   = 1200; // Kondisi jenuh di air (100% VWC)

// ── 3. KONFIGURASI NETWORK & SERVER ────────────────────────────────────────
const char* DEVICE_HOSTNAME = "Kelompok-Nutrix";
const char* DEVICE_NAME     = "Kelompok Nutrix";

// Server Endpoint Railway
const char* serverUrl = "https://nutrix-app-production.up.railway.app/api/iot/telemetry";

// Token Pairing dari Dashboard Web NUTRIX
char custom_device_token[65] = "";
char custom_sensor_id[32]    = "NUTRIX-DUAL-01";
Preferences preferences;
String pendingCommandId;
String pendingCommandStatus;
String lastExecutedCommandId;
String relayState = "off";

unsigned long previousMillis = 0;
const long telemetryInterval = 5000; // Kirim tiap 5 detik

// ── PROTOTYPE HELPER FUNCTIONS ─────────────────────────────────────────────
int getFilteredADC(int pin);
float calculateVWC(int rawADC, int dryVal, int wetVal);
void bacaSensorDanKirimKeWeb();
void beepSuccess();

void setup() {
    Serial.begin(115200);
    delay(1000);
    Serial.println("\n=======================================================");
    Serial.println("  NUTRIX PRECISION IoT CONTROLLER (ESP32 - SCOPUS GRADE)");
    Serial.println("=======================================================");
    Serial.printf("Device Name : %s\n", DEVICE_NAME);
    Serial.printf("Sensor 1    : Capacitive V2.0  (GPIO %d)\n", PIN_CAPACITIVE);
    Serial.printf("Sensor 2    : Resistive HD-38  (GPIO %d)\n", PIN_RESISTIVE);

    // Setup Pin Output
    pinMode(PIN_RELAY, OUTPUT);
    pinMode(PIN_BUZZER, OUTPUT);
    pinMode(PIN_LED_STATUS, OUTPUT);

    // Setup Pin Input ADC
    pinMode(PIN_CAPACITIVE, INPUT);
    pinMode(PIN_RESISTIVE, INPUT);

    // Relay Standby OFF (Active LOW -> Set HIGH)
    digitalWrite(PIN_RELAY, HIGH);
    digitalWrite(PIN_BUZZER, LOW);
    digitalWrite(PIN_LED_STATUS, LOW);

    preferences.begin("nutrix", false);
    preferences.getString("token", "").toCharArray(custom_device_token, sizeof(custom_device_token));
    preferences.getString("sensor", "NUTRIX-DUAL-01").toCharArray(custom_sensor_id, sizeof(custom_sensor_id));

    // WiFiManager menyimpan kredensial jaringan di NVS ESP32.
    WiFi.mode(WIFI_STA);
    WiFi.setHostname(DEVICE_HOSTNAME);
    digitalWrite(PIN_LED_STATUS, HIGH);

    WiFiManager wm;
    wm.setConfigPortalTimeout(180);
    WiFiManagerParameter custom_token("device_token", "Token Pairing dari Dashboard", custom_device_token, sizeof(custom_device_token));
    WiFiManagerParameter custom_sensor("sensor_id", "ID Sensor ESP32", custom_sensor_id, sizeof(custom_sensor_id));
    wm.addParameter(&custom_token);
    wm.addParameter(&custom_sensor);

    const bool wifiConnected = strlen(custom_device_token) > 0
        ? wm.autoConnect("NUTRIX-ESP32-PAIR")
        : wm.startConfigPortal("NUTRIX-ESP32-PAIR");
    if (!wifiConnected) {
        Serial.println("[ERROR] Wi-Fi belum dikonfigurasi. Nyalakan ulang untuk membuka portal pairing.");
        digitalWrite(PIN_RELAY, HIGH);
        delay(2000);
        ESP.restart();
    }

    strncpy(custom_device_token, custom_token.getValue(), sizeof(custom_device_token) - 1);
    custom_device_token[sizeof(custom_device_token) - 1] = '\0';
    strncpy(custom_sensor_id, custom_sensor.getValue(), sizeof(custom_sensor_id) - 1);
    custom_sensor_id[sizeof(custom_sensor_id) - 1] = '\0';
    preferences.putString("token", custom_device_token);
    preferences.putString("sensor", custom_sensor_id);

    Serial.println("[WIFI] Terhubung ke jaringan. Token pairing tersimpan secara lokal.");
    Serial.print("[WIFI] IP ESP32: "); Serial.println(WiFi.localIP());
    beepSuccess();
}

void loop() {
    unsigned long currentMillis = millis();

    if (currentMillis - previousMillis >= telemetryInterval) {
        previousMillis = currentMillis;

        if (WiFi.status() == WL_CONNECTED) {
            bacaSensorDanKirimKeWeb();
        } else {
            Serial.println("[WIFI] Koneksi terputus! Mencoba rekoneksi...");
            digitalWrite(PIN_LED_STATUS, LOW);
            WiFi.reconnect();
        }
    }
}

/**
 * Trimmed-Mean Filter: 20 sampel oversampling, buang 4 terendah & 4 tertinggi.
 * Menghasilkan nilai ADC stabil bebas noise lonjakan listrik / EMI.
 */
int getFilteredADC(int pin) {
    const int TOTAL_SAMPLES = 20;
    const int DROP_EXTREME  = 4; // Buang 4 bawah & 4 atas
    int samples[TOTAL_SAMPLES];

    for (int i = 0; i < TOTAL_SAMPLES; i++) {
        samples[i] = analogRead(pin);
        delay(5);
    }

    // Sort ascending (Insertion Sort)
    for (int i = 1; i < TOTAL_SAMPLES; i++) {
        int key = samples[i];
        int j = i - 1;
        while (j >= 0 && samples[j] > key) {
            samples[j + 1] = samples[j];
            j = j - 1;
        }
        samples[j + 1] = key;
    }

    // Hitung rata-rata sampel tengah (12 sampel)
    long sum = 0;
    int count = TOTAL_SAMPLES - (2 * DROP_EXTREME);
    for (int i = DROP_EXTREME; i < TOTAL_SAMPLES - DROP_EXTREME; i++) {
        sum += samples[i];
    }

    return (int)(sum / count);
}

/**
 * Konversi Raw ADC ke Volumetric Water Content (VWC) %
 */
float calculateVWC(int rawADC, int dryVal, int wetVal) {
    // Sensor kelembapan tanah analog: ADC tinggi = kering, ADC rendah = basah
    float vwc = ((float)(dryVal - rawADC) / (float)(dryVal - wetVal)) * 100.0;
    return constrain(vwc, 0.0, 100.0);
}

void bacaSensorDanKirimKeWeb() {
    digitalWrite(PIN_LED_STATUS, HIGH);

    // 1. Akuisisi Sinyal Sensor 1 (Capacitive V2.0 di D34)
    int rawCapacitive = getFilteredADC(PIN_CAPACITIVE);
    float vwcCapacitive = calculateVWC(rawCapacitive, CAP_ADC_DRY, CAP_ADC_WET);
    float voltCapacitive = (rawCapacitive / 4095.0) * 3.3;

    // 2. Akuisisi Sinyal Sensor 2 (Resistive HD-38 di D35)
    int rawResistive = getFilteredADC(PIN_RESISTIVE);
    float vwcResistive = calculateVWC(rawResistive, RES_ADC_DRY, RES_ADC_WET);
    float voltResistive = (rawResistive / 4095.0) * 3.3;

    // 3. Konsensus Rata-rata Ilmiah
    float avgMoisture = (vwcCapacitive + vwcResistive) / 2.0;
    float deviation = abs(vwcCapacitive - vwcResistive);

    Serial.println("\n-------------------------------------------------------");
    Serial.printf("[SENSOR 1 - CAPACITIVE D34] Raw: %4d | Volt: %.2fV | VWC: %.1f%%\n", rawCapacitive, voltCapacitive, vwcCapacitive);
    Serial.printf("[SENSOR 2 - RESISTIVE  D35] Raw: %4d | Volt: %.2fV | VWC: %.1f%%\n", rawResistive, voltResistive, vwcResistive);
    Serial.printf("[KONSENSUS FINAL] Rata-rata: %.1f%% | Deviasi: %.1f%%\n", avgMoisture, deviation);

    // 4. Siapkan Payload JSON Komprehensif
    StaticJsonDocument<768> jsonDoc;
    jsonDoc["device_token"]     = custom_device_token;
    jsonDoc["device_name"]      = DEVICE_NAME;
    jsonDoc["sensor_id"]        = custom_sensor_id;
    jsonDoc["hostname"]         = DEVICE_HOSTNAME;
    jsonDoc["wifi_ssid"]        = WiFi.SSID();
    jsonDoc["moisture"]         = round(avgMoisture * 10) / 10.0; // Nilai utama
    jsonDoc["wifi_rssi"]        = WiFi.RSSI();
    jsonDoc["ip_address"]       = WiFi.localIP().toString();

    // Nested Object: Detail Per-Sensor
    JsonObject sensors = jsonDoc.createNestedObject("sensors");

    JsonObject sCap = sensors.createNestedObject("capacitive_v2");
    sCap["gpio"]       = PIN_CAPACITIVE;
    sCap["raw_adc"]    = rawCapacitive;
    sCap["voltage"]    = round(voltCapacitive * 100) / 100.0;
    sCap["moisture"]   = round(vwcCapacitive * 10) / 10.0;

    JsonObject sRes = sensors.createNestedObject("resistive_hd38");
    sRes["gpio"]       = PIN_RESISTIVE;
    sRes["raw_adc"]    = rawResistive;
    sRes["voltage"]    = round(voltResistive * 100) / 100.0;
    sRes["moisture"]   = round(vwcResistive * 10) / 10.0;

    if (pendingCommandId.length() > 0) {
        jsonDoc["last_command_id"] = pendingCommandId;
        jsonDoc["last_command_status"] = pendingCommandStatus;
        jsonDoc["relay_state"] = relayState;
    }

    String requestBody;
    serializeJson(jsonDoc, requestBody);

    // 5. Transmisi ke Cloud API Railway
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.setTimeout(4500);

    int httpResponseCode = http.POST(requestBody);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.printf("[CLOUD] Respon [%d]: %s\n", httpResponseCode, response.c_str());

        // The next successful POST confirms that the server received a pending acknowledgement.
        StaticJsonDocument<256> respDoc;
        if (httpResponseCode == 200 && !deserializeJson(respDoc, response)) {
            pendingCommandId = "";
            pendingCommandStatus = "";

            const char* valveCmd = respDoc["commands"]["water_valve"];
            const char* commandId = respDoc["commands"]["command_id"];
            int durationSec = respDoc["commands"]["duration_sec"] | 0;

            if (valveCmd && commandId && String(valveCmd) == "ON" && durationSec > 0 && String(commandId) != lastExecutedCommandId) {
                durationSec = constrain(durationSec, 1, 10);
                Serial.printf(">>> [AKTUATOR] MENJALANKAN IRIGASI: %d DETIK <<<\n", durationSec);
                digitalWrite(PIN_RELAY, LOW); // ON Relay
                relayState = "on";
                digitalWrite(PIN_BUZZER, HIGH);
                delay(300);
                digitalWrite(PIN_BUZZER, LOW);

                delay(durationSec * 1000);

                digitalWrite(PIN_RELAY, HIGH); // OFF Relay
                relayState = "off";
                lastExecutedCommandId = commandId;
                pendingCommandId = commandId;
                pendingCommandStatus = "executed";
                Serial.println(">>> [AKTUATOR] IRIGASI SELESAI <<<");
            } else {
                digitalWrite(PIN_RELAY, HIGH);
                relayState = "off";
            }
        }
    } else {
        Serial.printf("[ERROR] Gagal kirim HTTP: %d\n", httpResponseCode);
    }

    http.end();
    digitalWrite(PIN_LED_STATUS, LOW);
}

void beepSuccess() {
    digitalWrite(PIN_BUZZER, HIGH); delay(80); digitalWrite(PIN_BUZZER, LOW); delay(80);
    digitalWrite(PIN_BUZZER, HIGH); delay(80); digitalWrite(PIN_BUZZER, LOW);
}
