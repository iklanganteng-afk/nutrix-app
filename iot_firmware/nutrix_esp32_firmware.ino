/**
 * ==============================================================================
 * SISTEM IoT NUTRIX — SMART AGRICULTURE WIRELESS CONTROLLER (ESP32)
 * ==============================================================================
 * Versi     : 2.0 (Dual Sensor — Kapasitif + Resistif)
 * Kelompok  : Nutrix
 * ==============================================================================
 * Fitur Utama:
 * 1. WiFi Captive Portal (WiFiManager): Setting WiFi & Pairing Token lewat HP
 * 2. Fallback WiFi Otomatis ke Hotspot "GG" jika Captive Portal timeout
 * 3. Hostname Jaringan: "Kelompok Nutrix" (muncul di daftar perangkat WiFi)
 * 4. Dual Sensor Kelembapan Tanah:
 *    - Sensor KAPASITIF (Capacitive Soil Moisture V2.0) → GPIO 34
 *    - Sensor RESISTIF  (YL-69 / Fork Probe)           → GPIO 35
 * 5. Rata-rata pembacaan kedua sensor untuk akurasi lebih tinggi
 * 6. Autentikasi Kredensial via Claim Token (device_token) dari Dashboard
 * 7. Kontrol Relay Solenoid Valve Otomatis dari Cloud Decision Engine (GPIO 26)
 * 8. Buzzer Bip Alert & LED Indikator Status WiFi & Transmisi Data
 * 9. Komunikasi Aman HTTPS POST JSON ke Railway Cloud Server API NUTRIX
 * 10. Penyimpanan Kredensial Persisten (Preferences / NVS Flash)
 * ==============================================================================
 *
 * LIBRARY YANG DIBUTUHKAN (Install di Arduino IDE Library Manager):
 * ─────────────────────────────────────────────────────────────────
 * 1. WiFiManager     — oleh: tzapu          (cari "WiFiManager" by tzapu)
 * 2. ArduinoJson     — oleh: Benoit Blanchon (cari "ArduinoJson" versi 6.x)
 *
 * BOARD MANAGER:
 * ─────────────────────────────────────────────────────────────────
 * ESP32 by Espressif Systems (URL: https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json)
 * Pilih Board: "DOIT ESP32 DEVKIT V1" atau "ESP32 Dev Module"
 *
 * ==============================================================================
 */

#include <WiFi.h>
#include <WiFiClientSecure.h>  // WAJIB untuk koneksi HTTPS (TLS/SSL)
#include <HTTPClient.h>
#include <ESPmDNS.h>           // mDNS Responder agar bisa diakses via kelompok-nutrix.local
#include <WiFiManager.h>       // Library WiFiManager oleh tzapu
#include <ArduinoJson.h>       // Library ArduinoJson oleh Benoit Blanchon (versi 6.x)
#include <Preferences.h>       // Penyimpanan NVS Flash (bawaan ESP32)

// ── 1. DEFINISI PIN ESP32 ──────────────────────────────────────────────────
// Sensor 1: Capacitive Soil Moisture Sensor V2.0.0 (Analog 0 - 3.0V)
// Pinout: GND -> GND, VCC -> 3.3V/5V, AV0T/AOUT -> GPIO 34 (D34)
#define PIN_MOISTURE_CAP  34   // Sensor KAPASITIF V2.0 — ADC1 Channel 6

// Sensor 2: Resistive Soil Moisture Sensor (Modul Driver HD-38 / LM393 + Probe)
// Pinout: GND -> GND, VCC -> 3.3V/5V, AO -> GPIO 35 (D35)
// CATATAN: Wajib ke GPIO 35 agar TIDAK tabrakan dengan GPIO 34 milik sensor kapasitif!
#define PIN_MOISTURE_RES  35   // Sensor RESISTIF HD-38 — ADC1 Channel 7

// Modul Relay (Pengendali Keran Solenoid Valve / Pompa Air Otomatis)
// Pinout: GND -> GND, VCC -> 5V, IN/Signal -> GPIO 26 (D26)
#define PIN_RELAY         26   // Modul Relay (Keran Air / Pompa Irigasi)
#define PIN_BUZZER        27   // Buzzer Aktif 5V
#define PIN_LED_STATUS    2    // LED Onboard ESP32 (Indikator Status Jaringan)

// ── 2. KONFIGURASI SERVER RAILWAY NUTRIX ───────────────────────────────────
const char* serverUrl = "https://nutrix-app-production.up.railway.app/api/iot/telemetry";

// ── HOSTNAME PERANGKAT DI JARINGAN ─────────────────────────────────────────
const char* DEVICE_HOSTNAME = "Kelompok-Nutrix";

// Parameter Pairing Token & Sensor ID
// Token ini didapatkan dari dashboard web NUTRIX (Contoh format: NTX-XXXXXXXXXXXX)
// Buffer 65 karakter = max 64 karakter token + 1 null terminator (sesuai kolom DB)
char custom_device_token[65] = "";
char custom_sensor_id[32]    = "NUTRIX-DUAL-01";
String pendingCommandId;
String pendingCommandStatus;
String lastExecutedCommandId;
String relayState = "off";

// Persistent storage (NVS Flash)
Preferences preferences;

// Interval pengiriman data telemetri (Default: 5000ms = 5 detik)
unsigned long previousMillis = 0;
const long interval = 5000;

// WiFiClientSecure global (untuk koneksi HTTPS)
WiFiClientSecure secureClient;

// ── Kalibrasi ADC per Sensor ───────────────────────────────────────────────
// Sensor KAPASITIF (Capacitive V2.0):
//   Kering (udara): ~3200 – 3600    Basah (air): ~1300 – 1700
const int CAP_DRY  = 3400;
const int CAP_WET  = 1500;

// Sensor RESISTIF (YL-69 / Fork Probe):
//   Kering (udara): ~3800 – 4095    Basah (air): ~800 – 1500
const int RES_DRY  = 4000;
const int RES_WET  = 1200;

// ── FORWARD DECLARATIONS ───────────────────────────────────────────────────
void bacaSensorDanKirimKeWeb();
void beepSuccess();
void beepError();
float bacaSensorOversampled(int pin, int samples);
float kalibrasePersen(float rawAdc, int dryVal, int wetVal);

// ════════════════════════════════════════════════════════════════════════════
//  SETUP — Inisialisasi sistem saat ESP32 pertama kali dinyalakan
// ════════════════════════════════════════════════════════════════════════════
void setup() {
    Serial.begin(115200);
    delay(1000);
    Serial.println("\n╔═══════════════════════════════════════════════════════╗");
    Serial.println("║   NUTRIX SMART AGRICULTURE IoT CONTROLLER (ESP32)    ║");
    Serial.println("║   Versi 2.0 — Dual Sensor (Kapasitif + Resistif)     ║");
    Serial.println("║   Kelompok Nutrix                                    ║");
    Serial.println("╚═══════════════════════════════════════════════════════╝");

    // Inisialisasi Hardware Pin
    pinMode(PIN_RELAY, OUTPUT);
    pinMode(PIN_BUZZER, OUTPUT);
    pinMode(PIN_LED_STATUS, OUTPUT);

    // Default kondisi: Relay OFF (Active LOW relay -> set HIGH)
    digitalWrite(PIN_RELAY, HIGH);
    digitalWrite(PIN_BUZZER, LOW);
    digitalWrite(PIN_LED_STATUS, LOW);

    // ── SET HOSTNAME AGAR MUNCUL DI JARINGAN SEBAGAI "Kelompok-Nutrix" ───
    WiFi.mode(WIFI_STA);
    WiFi.setHostname(DEVICE_HOSTNAME);

    // ── LOAD KREDENSIAL TERSIMPAN DARI NVS FLASH ─────────────────────────
    preferences.begin("nutrix", false);
    String savedToken  = preferences.getString("token", "");
    String savedSensor = preferences.getString("sensor", "NUTRIX-DUAL-01");

    savedToken.toCharArray(custom_device_token, sizeof(custom_device_token));
    savedSensor.toCharArray(custom_sensor_id, sizeof(custom_sensor_id));

    if (savedToken.length() > 0) {
        Serial.println("[NVS] Token pairing tersimpan.");
    } else {
        Serial.println("[NVS] Belum ada token tersimpan. Siapkan di Captive Portal.");
    }

    // ── 3. WIFIMANAGER CAPTIVE PORTAL SETUP & PAIRING ────────────────────
    WiFiManager wm;

    // WiFiManager menyimpan kredensial Wi-Fi; NVS menyimpan token pairing.
    wm.setConfigPortalTimeout(180);

    // Custom input form di Captive Portal HP:
    WiFiManagerParameter custom_param_token("device_token", "Device Pairing Token (Dari Web NUTRIX)", custom_device_token, 65);
    WiFiManagerParameter custom_param_sensor("sensor_id", "Sensor Node ID", custom_sensor_id, 32);

    wm.addParameter(&custom_param_token);
    wm.addParameter(&custom_param_sensor);

    Serial.println("[WIFI] Memeriksa konfigurasi WiFi & Kredensial...");
    Serial.println("[WIFI] Captive Portal SSID: \"Kelompok Nutrix - ESP32\"");
    Serial.println("[WIFI] Jika belum dikonfigurasi, sambungkan HP ke portal pairing ESP32.");
    digitalWrite(PIN_LED_STATUS, HIGH); // Nyalakan LED saat portal/koneksi aktif

    // ── ESP32 membuat WiFi Access Point: "Kelompok Nutrix - ESP32" ─────────
    const bool wifiConnected = strlen(custom_device_token) > 0
        ? wm.autoConnect("Kelompok Nutrix - ESP32")
        : wm.startConfigPortal("Kelompok Nutrix - ESP32");
    if (!wifiConnected) {
        Serial.println("[ERROR] Pairing Wi-Fi gagal atau timeout. Nyalakan ulang untuk mencoba lagi.");
        digitalWrite(PIN_RELAY, HIGH);
        beepError();
        delay(2000);
        ESP.restart();
    }

    // Ambil data konfigurasi dari portal (jika user mengisi lewat Captive Portal)
    if (strlen(custom_param_token.getValue()) > 0) {
        strncpy(custom_device_token, custom_param_token.getValue(), sizeof(custom_device_token) - 1);
        custom_device_token[sizeof(custom_device_token) - 1] = '\0';
    }
    strncpy(custom_sensor_id, custom_param_sensor.getValue(), sizeof(custom_sensor_id) - 1);
    custom_sensor_id[sizeof(custom_sensor_id) - 1] = '\0';

    // ── SIMPAN KREDENSIAL KE NVS FLASH (Persisten) ───────────────────────
    if (strlen(custom_device_token) > 0) {
        preferences.putString("token", String(custom_device_token));
    }
    preferences.putString("sensor", String(custom_sensor_id));

    // ── Pastikan hostname terdaftar di router / hotspot HP ───────────────
    WiFi.setHostname(DEVICE_HOSTNAME);

    // ── Inisialisasi mDNS Responder (kelompok-nutrix.local) ───────────────
    if (MDNS.begin("kelompok-nutrix")) {
        MDNS.setInstanceName("Kelompok Nutrix - Smart Agriculture");
        Serial.println("[MDNS] Responder aktif! Akses via: http://kelompok-nutrix.local");
    }

    Serial.println("\n╔═══════════════════════════════════════════════════════╗");
    Serial.println("║           >>> SUKSES TERHUBUNG KE WIFI! <<<           ║");
    Serial.println("╚═══════════════════════════════════════════════════════╝");
    Serial.print("  Nama Perangkat   : Kelompok Nutrix\n");
    Serial.print("  Hostname Jaringan: "); Serial.println(DEVICE_HOSTNAME);
    Serial.print("  mDNS Address     : http://kelompok-nutrix.local\n");
    Serial.print("  SSID Terhubung   : "); Serial.println(WiFi.SSID());
    Serial.print("  IP Address ESP32 : "); Serial.println(WiFi.localIP());
    Serial.print("  Node ID          : "); Serial.println(custom_sensor_id);
    Serial.println("  Pairing Token    : tersimpan di NVS (tidak dicetak)");
    Serial.println("  Sensor Kapasitif : GPIO 34 (ADC1)");
    Serial.println("  Sensor Resistif  : GPIO 35 (ADC1)");
    Serial.println("  Relay            : GPIO 26");
    Serial.println("  Buzzer           : GPIO 27");

    // ── SETUP HTTPS CLIENT (TLS tanpa validasi sertifikat) ───────────────
    secureClient.setInsecure();

    // Indikator audio 2x bip pertanda hardware siap
    beepSuccess();
    Serial.println("\n[SYSTEM] Sistem siap! Mulai streaming telemetri setiap 5 detik...\n");
}

// ════════════════════════════════════════════════════════════════════════════
//  LOOP — Siklus utama pengiriman data telemetri
// ════════════════════════════════════════════════════════════════════════════
void loop() {
    unsigned long currentMillis = millis();

    if (currentMillis - previousMillis >= interval) {
        previousMillis = currentMillis;

        if (WiFi.status() == WL_CONNECTED) {
            bacaSensorDanKirimKeWeb();
        } else {
            Serial.println("[WIFI] Koneksi terputus! Mencoba rekoneksi...");
            digitalWrite(PIN_LED_STATUS, LOW);

            // Coba reconnect ke WiFi yang sudah tersimpan
            WiFi.reconnect();
            delay(2000);

        }
    }
}

// ════════════════════════════════════════════════════════════════════════════
//  UTILITAS: Oversampled ADC Read (rata-rata N sampel untuk stabilitas)
// ════════════════════════════════════════════════════════════════════════════
float bacaSensorOversampled(int pin, int samples) {
    long total = 0;
    for (int i = 0; i < samples; i++) {
        total += analogRead(pin);
        delay(10);
    }
    return (float)total / (float)samples;
}

// ════════════════════════════════════════════════════════════════════════════
//  UTILITAS: Konversi nilai ADC mentah ke persentase kelembapan (0-100%)
// ════════════════════════════════════════════════════════════════════════════
float kalibrasePersen(float rawAdc, int dryVal, int wetVal) {
    // map() hanya integer, kita pakai rumus float manual
    float pct = (float)(dryVal - rawAdc) / (float)(dryVal - wetVal) * 100.0;
    return constrain(pct, 0.0, 100.0);
}

// ════════════════════════════════════════════════════════════════════════════
//  AKUISISI DUAL SENSOR & PENGIRIMAN DATA TELEMETRI KE CLOUD
// ════════════════════════════════════════════════════════════════════════════
void bacaSensorDanKirimKeWeb() {
    digitalWrite(PIN_LED_STATUS, HIGH);

    if (strlen(custom_device_token) == 0) {
        Serial.println("[PAIRING] Token belum tersedia. Pair ESP32 dari captive portal.");
        digitalWrite(PIN_LED_STATUS, LOW);
        return;
    }

    // ── 1. Baca Sensor KAPASITIF (GPIO 34) ───────────────────────────────
    float rawCap = bacaSensorOversampled(PIN_MOISTURE_CAP, 10);
    float moistureCap = kalibrasePersen(rawCap, CAP_DRY, CAP_WET);

    // ── 2. Baca Sensor RESISTIF (GPIO 35) ────────────────────────────────
    float rawRes = bacaSensorOversampled(PIN_MOISTURE_RES, 10);
    float moistureRes = kalibrasePersen(rawRes, RES_DRY, RES_WET);

    // ── 3. Gabungan rata-rata kedua sensor (sensor fusion sederhana) ─────
    float moistureAvg = (moistureCap + moistureRes) / 2.0;
    moistureAvg = constrain(moistureAvg, 0.0, 100.0);

    // ── Tampilkan di Serial Monitor ──────────────────────────────────────
    Serial.println("\n┌───────────────────────────────────────────────────────┐");
    Serial.println("│              PEMBACAAN SENSOR KELEMBAPAN              │");
    Serial.println("├───────────────────────────────────────────────────────┤");
    Serial.print("│  Kapasitif (GPIO 34) : ADC "); Serial.print((int)rawCap);
    Serial.print("  →  "); Serial.print(moistureCap, 1); Serial.println("%");
    Serial.print("│  Resistif  (GPIO 35) : ADC "); Serial.print((int)rawRes);
    Serial.print("  →  "); Serial.print(moistureRes, 1); Serial.println("%");
    Serial.println("├───────────────────────────────────────────────────────┤");
    Serial.print("│  RATA-RATA GABUNGAN  : "); Serial.print(moistureAvg, 1); Serial.println("%");
    Serial.println("└───────────────────────────────────────────────────────┘");

    // ── 4. Siapkan Payload JSON ──────────────────────────────────────────
    StaticJsonDocument<768> jsonDoc;
    jsonDoc["device_token"] = custom_device_token;
    jsonDoc["sensor_id"]     = custom_sensor_id;
    jsonDoc["device_name"]   = "Kelompok Nutrix";
    jsonDoc["hostname"]      = DEVICE_HOSTNAME;
    jsonDoc["ip_address"]    = WiFi.localIP().toString();
    jsonDoc["wifi_ssid"]     = WiFi.SSID();
    jsonDoc["wifi_rssi"]     = WiFi.RSSI();
    
    // Nilai Kelembapan Rata-Rata Gabungan (Sensor Fusion)
    jsonDoc["moisture"]      = round(moistureAvg * 10) / 10.0;
    
    // Nilai Kelembapan Terpisah per Sensor
    jsonDoc["moisture_cap"]  = round(moistureCap * 10) / 10.0; // Kapasitif GPIO 34
    jsonDoc["moisture_res"]  = round(moistureRes * 10) / 10.0; // Resistif GPIO 35
    
    // Nilai Mentah ADC untuk Kalibrasi & Diagnostik
    jsonDoc["raw_cap"]       = (int)rawCap;
    jsonDoc["raw_res"]       = (int)rawRes;
    if (pendingCommandId.length() > 0) {
        jsonDoc["last_command_id"] = pendingCommandId;
        jsonDoc["last_command_status"] = pendingCommandStatus;
        jsonDoc["relay_state"] = relayState;
    }

    String requestBody;
    serializeJson(jsonDoc, requestBody);

    // ── 5. Kirim HTTPS POST ke Cloud Railway NUTRIX ──────────────────────
    HTTPClient http;
    http.begin(secureClient, serverUrl);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");
    http.setTimeout(10000); // 10 detik timeout

    Serial.print("[CLOUD] POST → "); Serial.println(serverUrl);
    Serial.print("[CLOUD] Payload: "); Serial.println(requestBody);

    int httpResponseCode = http.POST(requestBody);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.print("[CLOUD] Respon ["); Serial.print(httpResponseCode); Serial.println("]:");
        Serial.println(response);

        // Panduan debug respon:
        // 200 = Sukses, data telemetri tersimpan
        // 401 = Token salah atau kadaluarsa (cek ulang di dashboard web)
        // 422 = Field tidak valid / moisture tidak dikirim
        // 404 = Route /api/iot/telemetry belum ada di server

        // ── 6. Parsing Perintah Kendali Otomatis dari Cloud Decision Engine
        if (httpResponseCode == 200) {
            StaticJsonDocument<512> responseDoc;
            DeserializationError error = deserializeJson(responseDoc, response);

            if (!error) {
                pendingCommandId = "";
                pendingCommandStatus = "";
                const char* waterValve = responseDoc["commands"]["water_valve"];
                const char* commandId = responseDoc["commands"]["command_id"];
                int durationSec        = responseDoc["commands"]["duration_sec"] | 0;
                bool buzzerAlert       = responseDoc["commands"]["buzzer_alert"] | false;

                if (waterValve != nullptr && commandId != nullptr && String(waterValve) == "ON"
                    && durationSec > 0 && String(commandId) != lastExecutedCommandId) {
                    durationSec = constrain(durationSec, 1, 10);
                    Serial.println("\n╔═══════════════════════════════════════════════════════╗");
                    Serial.println("║   >>> PERINTAH CLOUD: BUKA VALVE RELAY (SIRAM)! <<<   ║");
                    Serial.println("╚═══════════════════════════════════════════════════════╝");
                    Serial.print("  Durasi penyiraman: "); Serial.print(durationSec); Serial.println(" detik");

                    // Aktifkan Relay (Active LOW)
                    digitalWrite(PIN_RELAY, LOW);
                    relayState = "on";

                    // Buzzer alert singkat
                    if (buzzerAlert) {
                        digitalWrite(PIN_BUZZER, HIGH);
                        delay(600);
                        digitalWrite(PIN_BUZZER, LOW);
                    }

                    // Jalankan penyiraman sesuai durasi dari cloud
                    delay(durationSec * 1000);

                    // Tutup kembali relay
                    digitalWrite(PIN_RELAY, HIGH);
                    relayState = "off";
                    lastExecutedCommandId = commandId;
                    pendingCommandId = commandId;
                    pendingCommandStatus = "executed";
                    Serial.println("[AKTUATOR] Penyiraman selesai. Relay kembali STANDBY.\n");
                } else {
                    digitalWrite(PIN_RELAY, HIGH); // Pastikan relay selalu tertutup
                    relayState = "off";
                }
            }
        }
    } else {
        Serial.print("[ERROR] Gagal HTTPS POST, kode error: ");
        Serial.println(httpResponseCode);
        Serial.println("  → -1  = Gagal koneksi TLS (cek WiFi / server down)");
        Serial.println("  → -11 = Timeout (server terlalu lama merespon)");
        beepError();
    }

    http.end();
    digitalWrite(PIN_LED_STATUS, LOW);
}

// ════════════════════════════════════════════════════════════════════════════
//  INDIKATOR SUARA BUZZER
// ════════════════════════════════════════════════════════════════════════════
void beepSuccess() {
    // 2x bip pendek = sukses
    for (int i = 0; i < 2; i++) {
        digitalWrite(PIN_BUZZER, HIGH);
        delay(100);
        digitalWrite(PIN_BUZZER, LOW);
        delay(100);
    }
}

void beepError() {
    // 3x bip panjang = error
    for (int i = 0; i < 3; i++) {
        digitalWrite(PIN_BUZZER, HIGH);
        delay(300);
        digitalWrite(PIN_BUZZER, LOW);
        delay(150);
    }
}
