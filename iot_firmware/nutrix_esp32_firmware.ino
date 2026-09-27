/**
 * ==============================================================================
 * SISTEM IoT NUTRIX — SMART AGRICULTURE WIRELESS CONTROLLER (ESP32)
 * ==============================================================================
 * Fitur Utama:
 * 1. WiFi Captive Portal (WiFiManager): Setting WiFi & Pairing Token lewat HP
 * 2. Autentikasi Kredensial via Claim Token (device_token) yang digenerate Dashboard
 * 3. Sensor Kelembapan Tanah (Analog Moisture Sensor ADC1 GPIO 34)
 * 4. Kontrol Relay Solenoid Valve Otomatis dari Cloud Decision Engine (GPIO 26)
 * 5. Buzzer Bip Alert & LED Indikator Status WiFi & Transmisi Data
 * 6. Komunikasi Aman HTTP/HTTPS POST JSON ke Railway Cloud Server API NUTRIX
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiManager.h>      // Library WiFiManager oleh tzapu
#include <ArduinoJson.h>       // Library ArduinoJson oleh Benoit Blanchon

// ── 1. DEFINISI PIN ESP32 ──────────────────────────────────────────────────
#define PIN_MOISTURE    34     // Sensor Kelembapan Tanah (Analog ADC1)
#define PIN_RELAY       26     // Modul Relay (Keran Air / Pompa Irigasi)
#define PIN_BUZZER      27     // Buzzer Aktif 5V
#define PIN_LED_STATUS  2      // LED Onboard ESP32 (Indikator Status Jaringan)

// ── 2. KONFIGURASI SERVER RAILWAY NUTRIX ───────────────────────────────────
const char* serverUrl = "https://nutrix-app-production.up.railway.app/api/iot/telemetry";

// Parameter Pairing Token & Sensor ID
// Token ini didapatkan dari dashboard web NUTRIX (Contoh format: NTX-XXXXXXXXXXXX)
char custom_device_token[40] = "";
char custom_sensor_id[32]    = "ESP32-NODE-01";
char custom_taman_id[8]      = "1"; // Fallback opsional

// Interval pengiriman data telemetri (Default: 5000ms = 5 detik)
unsigned long previousMillis = 0;
const long interval = 5000; 

void setup() {
    Serial.begin(115200);
    delay(1000);
    Serial.println("\n=======================================================");
    Serial.println("     NUTRIX SMART AGRICULTURE IoT CONTROLLER (ESP32)   ");
    Serial.println("=======================================================");

    // Inisialisasi Hardware Pin
    pinMode(PIN_RELAY, OUTPUT);
    pinMode(PIN_BUZZER, OUTPUT);
    pinMode(PIN_LED_STATUS, OUTPUT);

    // Default kondisi: Relay OFF (Active LOW relay -> set HIGH)
    digitalWrite(PIN_RELAY, HIGH);
    digitalWrite(PIN_BUZZER, LOW);
    digitalWrite(PIN_LED_STATUS, LOW);

    // ── 3. WIFIMANAGER CAPTIVE PORTAL SETUP & PAIRING ────────────────────
    WiFiManager wm;

    // Custom input form di Captive Portal HP:
    WiFiManagerParameter custom_param_token("device_token", "Device Pairing Token (Dari Web NUTRIX)", custom_device_token, 40);
    WiFiManagerParameter custom_param_sensor("sensor_id", "Sensor Node ID", custom_sensor_id, 32);
    WiFiManagerParameter custom_param_taman("taman_id", "ID Taman (Fallback)", custom_taman_id, 8);

    wm.addParameter(&custom_param_token);
    wm.addParameter(&custom_param_sensor);
    wm.addParameter(&custom_param_taman);

    Serial.println("[WIFI] Memeriksa konfigurasi WiFi & Kredensial...");
    digitalWrite(PIN_LED_STATUS, HIGH); // Nyalakan LED saat portal/koneksi aktif

    // Jika belum terkoneksi, ESP32 membuat WiFi Access Point: "NUTRIX-ESP32-PAIR"
    // Pengguna membuka HP, hubungkan ke SSID "NUTRIX-ESP32-PAIR", dan masukkan Token Pairing
    if (!wm.autoConnect("NUTRIX-ESP32-PAIR")) {
        Serial.println("[ERROR] Gagal konek ke WiFi atau waktu habis. Merestart...");
        delay(3000);
        ESP.restart();
    }

    // Ambil data konfigurasi dari portal
    strncpy(custom_device_token, custom_param_token.getValue(), sizeof(custom_device_token));
    strncpy(custom_sensor_id, custom_param_sensor.getValue(), sizeof(custom_sensor_id));
    strncpy(custom_taman_id, custom_param_taman.getValue(), sizeof(custom_taman_id));

    Serial.println("\n>>> SUKSES TERHUBUNG KE WIFI! <<<");
    Serial.print("IP Address ESP32 : "); Serial.println(WiFi.localIP());
    Serial.print("Node ID          : "); Serial.println(custom_sensor_id);
    Serial.print("Pairing Token    : "); Serial.println(strlen(custom_device_token) > 0 ? custom_device_token : "(Kosong / Menggunakan Taman ID)");

    // Indikator audio 2x bip pertanda hardware siap
    beepSuccess();
}

void loop() {
    unsigned long currentMillis = millis();

    // Loop transmisi data telemetri berkala
    if (currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;

        if (WiFi.status() == WL_CONNECTED) {
            bacaSensorDanKirimKeWeb();
        } else {
            Serial.println("[WIFI] Koneksi terputus! Mencoba rekoneksi...");
            digitalWrite(PIN_LED_STATUS, LOW);
        }
    }
}

// ── 4. AKUISISI SENSOR TANAH & PENGIRIMAN DATA TELEMETRI KE CLOUD ──────────
void bacaSensorDanKirimKeWeb() {
    // Kedipkan LED status saat sedang mengirim paket
    digitalWrite(PIN_LED_STATUS, HIGH);

    // 1. Baca nilai analog dari Sensor Kelembapan Tanah (ADC1: 0 - 4095)
    // Lakukan oversampling 5 sampel untuk stabilitas pembacaan
    long adcSum = 0;
    for (int i = 0; i < 5; i++) {
        adcSum += analogRead(PIN_MOISTURE);
        delay(10);
    }
    int rawValue = adcSum / 5;

    // Kalibrasi ADC Capasitive / Resistive Soil Moisture:
    // Sensor kering (di udara): ~3500 - 4000
    // Sensor basah (di dalam air): ~1200 - 1500
    float moisturePct = map(rawValue, 3600, 1300, 0, 100);
    moisturePct = constrain(moisturePct, 0.0, 100.0);

    Serial.println("\n-------------------------------------------------------");
    Serial.print("[SENSOR] Raw ADC: "); Serial.print(rawValue);
    Serial.print(" | Kelembapan Tanah: "); Serial.print(moisturePct, 1); Serial.println("%");

    // 2. Siapkan Payload JSON
    StaticJsonDocument<256> jsonDoc;
    if (strlen(custom_device_token) > 0) {
        jsonDoc["device_token"] = custom_device_token;
    }
    jsonDoc["taman_id"]  = atoi(custom_taman_id);
    jsonDoc["sensor_id"] = custom_sensor_id;
    jsonDoc["moisture"]  = round(moisturePct * 10) / 10.0;

    String requestBody;
    serializeJson(jsonDoc, requestBody);

    // 3. Kirim HTTP POST ke Cloud Railway NUTRIX
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(4000);

    Serial.print("[CLOUD] POST Telemetri ke Railway: ");
    Serial.println(serverUrl);

    int httpResponseCode = http.POST(requestBody);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.print("[CLOUD] Respon ["); Serial.print(httpResponseCode); Serial.println("]:");
        Serial.println(response);

        // 4. Parsing Perintah Kendali Otomatis (Relay / Buzzer) dari Decision Engine Cloud
        StaticJsonDocument<512> responseDoc;
        DeserializationError error = deserializeJson(responseDoc, response);

        if (!error) {
            const char* waterValve = responseDoc["commands"]["water_valve"];
            int durationSec        = responseDoc["commands"]["duration_sec"] | 0;

            // Jika Cloud memerintahkan penyiraman otomatis
            if (String(waterValve) == "ON" && durationSec > 0) {
                Serial.println(">>> [AKTUATOR] PERINTAH CLOUD: BUKA VALVE RELAY! <<<");
                digitalWrite(PIN_RELAY, LOW); // Aktifkan Relay (Active LOW)
                digitalWrite(PIN_BUZZER, HIGH);
                delay(600);
                digitalWrite(PIN_BUZZER, LOW);

                // Jalankan penyiraman sesuai durasi kalkulasi cloud
                delay(durationSec * 1000);

                digitalWrite(PIN_RELAY, HIGH); // Tutup kembali relay
                Serial.println(">>> [AKTUATOR] PENYIRAMAN SELESAI. RELAY KEMBALI STANDBY. <<<");
            } else {
                digitalWrite(PIN_RELAY, HIGH); // Pastikan relay selalu tertutup
            }
        }
    } else {
        Serial.print("[ERROR] Gagal mengirim HTTP, Error code: ");
        Serial.println(httpResponseCode);
    }

    http.end();
    digitalWrite(PIN_LED_STATUS, LOW);
}

// ── INDIKATOR SUARA BUZZER ─────────────────────────────────────────────────
void beepSuccess() {
    digitalWrite(PIN_BUZZER, HIGH);
    delay(100);
    digitalWrite(PIN_BUZZER, LOW);
    delay(100);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(100);
    digitalWrite(PIN_BUZZER, LOW);
}
