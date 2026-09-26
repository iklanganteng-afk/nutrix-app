/**
 * ==============================================================================
 * SISTEM IoT NUTRIX — SMART AGRICULTURE WIRELESS CONTROLLER (ESP32)
 * ==============================================================================
 * Fitur:
 * 1. WiFi Captive Portal (WiFiManager): Setting WiFi lewat HP tanpa edit kodingan
 * 2. Sensor Kelembapan Tanah (Analog Moisture Sensor)
 * 3. Relay Valve (Keran Air Otomatis)
 * 4. Buzzer & LED Status Indikator
 * 5. Komunikasi HTTP POST JSON ke Railway Cloud API NUTRIX
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiManager.h>      // Library WiFiManager oleh tzapu
#include <ArduinoJson.h>       // Library ArduinoJson oleh Benoit Blanchon

// ── 1. DEFINISI PIN ESP32 ──────────────────────────────────────────────────
#define PIN_MOISTURE    34     // Sensor Kelembapan Tanah (Analog ADC1)
#define PIN_RELAY       26     // Modul Relay (Keran Air / Pompa)
#define PIN_BUZZER      27     // Buzzer Aktif
#define PIN_LED_STATUS  2      // LED Bawaan Board ESP32 (Indikator Status)

// ── 2. KONFIGURASI SERVER RAILWAY ──────────────────────────────────────────
const char* serverUrl = "https://nutrix-app-production.up.railway.app/api/iot/telemetry";

// Parameter ID Taman (Bisa diisi default atau disesuaikan dengan ID di Web)
char custom_taman_id[8] = "1";
char custom_sensor_id[32] = "ESP32-NUTRIX-01";

// Waktu interval pengiriman data ke server (misal tiap 5 detik)
unsigned long previousMillis = 0;
const long interval = 5000; 

void setup() {
    Serial.begin(115200);
    delay(1000);
    Serial.println("\n--- MEMULAI NUTRIX SMART AGRI IoT SYSTEM ---");

    // Inisialisasi Pin
    pinMode(PIN_RELAY, OUTPUT);
    pinMode(PIN_BUZZER, OUTPUT);
    pinMode(PIN_LED_STATUS, OUTPUT);

    // Default kondisi relay & buzzer mati (Relay aktif LOW pada umumnya)
    digitalWrite(PIN_RELAY, HIGH); // HIGH = MATI untuk modul relay active-low
    digitalWrite(PIN_BUZZER, LOW);
    digitalWrite(PIN_LED_STATUS, LOW);

    // ── 3. WIFIMANAGER CAPTIVE PORTAL SETUP ───────────────────────────────
    WiFiManager wm;

    // Tambahkan custom input untuk ID Taman & Sensor ID di halaman web HP
    WiFiManagerParameter custom_param_taman("taman_id", "ID Taman di Web NUTRIX", custom_taman_id, 8);
    WiFiManagerParameter custom_param_sensor("sensor_id", "Sensor ID", custom_sensor_id, 32);
    wm.addParameter(&custom_param_taman);
    wm.addParameter(&custom_param_sensor);

    Serial.println("Mengecek koneksi WiFi tersimpan...");
    // Nyalakan LED tanda sedang mencari / membuka portal
    digitalWrite(PIN_LED_STATUS, HIGH);

    // Jika belum ada WiFi tersimpan, ESP32 otomatis jadi Access Point: "NUTRIX-ESP32-SETUP"
    // Buka HP, sambungkan ke WiFi "NUTRIX-ESP32-SETUP", lalu isi form di browser HP!
    if (!wm.autoConnect("NUTRIX-ESP32-SETUP")) {
        Serial.println("Gagal terhubung ke WiFi atau timeout. Merestart ESP32...");
        delay(3000);
        ESP.restart();
    }

    // Jika berhasil tersambung:
    strcpy(custom_taman_id, custom_param_taman.getValue());
    strcpy(custom_sensor_id, custom_param_sensor.getValue());

    Serial.println("\n>>> SUKSES TERHUBUNG KE WIFI! <<<");
    Serial.print("IP Address ESP32: ");
    Serial.println(WiFi.localIP());

    // Indikator sukses: Bunyikan buzzer 2x bip pendek
    beepSuccess();
}

void loop() {
    unsigned long currentMillis = millis();

    // Jalankan siklus setiap interval (5 detik sekali)
    if (currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;

        if (WiFi.status() == WL_CONNECTED) {
            bacaSensorDanKirimKeWeb();
        } else {
            Serial.println("WiFi terputus! Mencoba menyambung kembali...");
            digitalWrite(PIN_LED_STATUS, LOW);
        }
    }
}

// ── 4. FUNGSI PEMBACAAN SENSOR & PENGIRIMAN DATA ────────────────────────────
void bacaSensorDanKirimKeWeb() {
    // 1. Baca nilai analog dari Sensor Kelembapan Tanah (Rentang ESP32 ADC: 0 - 4095)
    int rawValue = analogRead(PIN_MOISTURE);
    
    // Kalibrasi kasar: di udara kering ~3500-4000, di air basah ~1200-1500
    // Konversi ke persentase 0% - 100%
    float moisturePct = map(rawValue, 3600, 1200, 0, 100);
    moisturePct = constrain(moisturePct, 0.0, 100.0);

    Serial.println("\n-------------------------------------------");
    Serial.print("Nilai Sensor Mentah: "); Serial.println(rawValue);
    Serial.print("Kelembapan Tanah: "); Serial.print(moisturePct); Serial.println("%");

    // 2. Siapkan Payload JSON untuk Web NUTRIX
    StaticJsonDocument<256> jsonDoc;
    jsonDoc["taman_id"]  = atoi(custom_taman_id);
    jsonDoc["sensor_id"] = custom_sensor_id;
    jsonDoc["moisture"]  = moisturePct;

    String requestBody;
    serializeJson(jsonDoc, requestBody);

    // 3. Kirim HTTP POST ke Railway Cloud Server
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");

    Serial.print("Mengirim data ke Railway: ");
    Serial.println(serverUrl);

    int httpResponseCode = http.POST(requestBody);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.print("Respon HTTP ["); Serial.print(httpResponseCode); Serial.println("]:");
        Serial.println(response);

        // 4. Baca Keputusan Otak Website NUTRIX dari Respon JSON
        StaticJsonDocument<512> responseDoc;
        DeserializationError error = deserializeJson(responseDoc, response);

        if (!error) {
            const char* waterValve = responseDoc["commands"]["water_valve"];
            bool buzzerAlert = responseDoc["commands"]["buzzer_alert"];
            int durationSec = responseDoc["commands"]["duration_sec"];

            // Eksekusi Keran / Pompa Relay
            if (String(waterValve) == "ON") {
                Serial.println(">>> PERINTAH DARI WEB: BUKA KERAN (RELAY AKTIF)! <<<");
                digitalWrite(PIN_RELAY, LOW); // Aktifkan relay (Active LOW)
                digitalWrite(PIN_BUZZER, HIGH);
                delay(1000);
                digitalWrite(PIN_BUZZER, LOW);
                
                // Durasi siram
                delay(durationSec * 1000);
                digitalWrite(PIN_RELAY, HIGH); // Matikan kembali relay
                Serial.println(">>> PENYIRAMAN SELESAI. KERAN DITUTUP KEMBALI. <<<");
            } else {
                digitalWrite(PIN_RELAY, HIGH); // Pastikan relay mati
            }
        }
    } else {
        Serial.print("Gagal mengirim HTTP, Error code: ");
        Serial.println(httpResponseCode);
    }

    http.end();
}

void beepSuccess() {
    digitalWrite(PIN_BUZZER, HIGH);
    delay(100);
    digitalWrite(PIN_BUZZER, LOW);
    delay(100);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(100);
    digitalWrite(PIN_BUZZER, LOW);
}
