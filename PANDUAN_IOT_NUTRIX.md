# PANDUAN LENGKAP SISTEM IoT NUTRIX (ESP32 WIRELESS STREAMING)

Dokumen ini adalah panduan operasional lengkap dari tahap flashing firmware ESP32, konfigurasi wireless tanpa kabel (WiFiManager), pairing token ke web dashboard, hingga arsitektur otomatisasi keran irigasi (Relay).

---

## DAFTAR ISI
1. [Arsitektur Sistem IoT](#1-arsitektur-sistem-iot)
2. [Spesifikasi Hardware & Pinout ESP32](#2-spesifikasi-hardware--pinout-esp32)
3. [Alur Pembuatan Taman Baru di Dashboard](#3-alur-pembuatan-taman-baru-di-dashboard)
4. [Langkah Pairing ESP32 Tanpa Kabel (Wireless Portal)](#4-langkah-pairing-esp32-tanpa-kabel-wireless-portal)
5. [Fitur Reset Total Dashboard & Ganti Token](#5-fitur-reset-total-dashboard--ganti-token)
6. [Otomatisasi Keran (Relay) & Indikator LED](#6-otomatisasi-keran-relay--indikator-led)
7. [Troubleshooting & Solusi Kendala](#7-troubleshooting--solusi-kendala)

---

## 1. ARSITEKTUR SISTEM IoT

```
┌─────────────────────────────────────────────────────────────┐
│                      LOKASI KEBUN / LAHAN                   │
│                                                             │
│  [Sensor Capacitive D34 (S)] ──┐                            │
│  [Sensor Resistive  D35 (S)] ──┼──► [ESP32 Shield G-V-S]    │
│                                │             │              │
│  [Relay Solenoid Valve D2 (S)] ◄─────────────┘              │
└──────────────────────────────────────────────┬──────────────┘
                                               │ (WiFi 2.4 GHz HTTPS)
                                               ▼ POST /api/iot/telemetry
┌─────────────────────────────────────────────────────────────┐
│                     RAILWAY CLOUD SERVER                    │
│                                                             │
│   • Validasi Token Rahasia Perangkat (device_token)         │
│   • Simpan Telemetri ke Database PostgreSQL/MySQL           │
│   • TelemetryDecisionEngine (Analisa Skor Kesehatan Kebun)  │
│   • Respon Perintah Otomatis: Keran ON jika Kelembapan <30% │
└─────────────────────────────────┬───────────────────────────┘
                                  │
                                  ▼ WebSocket / Polling Stream
┌─────────────────────────────────────────────────────────────┐
│                    DASHBOARD WEB PENGGUNA                   │
│                                                             │
│   • Indikator Status: ONLINE / OFFLINE (Realtime Heartbeat) │
│   • 4 Kartu Sensor: Kelembapan, pH, Suhu, EC                │
│   • AI Health Score (0 - 100) & Rekomendasi Agronomi        │
│   • Tombol Kontrol Manual & Reset Total Dashboard           │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. SPESIFIKASI HARDWARE & PINOUT ESP32 EXPANSION SHIELD (G-V-S)

Firmware: `iot_firmware/nutrix_esp32_firmware.ino`

### Tabel Wiring Expansion Shield (G-V-S Ready):

| Komponen | Pin Shield | Pin Modul | Tipe Pin | Fungsi |
|---|---|---|---|---|
| **Sensor Capacitive V2.0** | **Baris D34** | `AOUT`→**S**, `VCC`→**V**, `GND`→**G** | ADC1_CH6 (GPIO 34) | Sensor kelembapan kapasitif (bebas korosi) |
| **Sensor Resistive HD-38** | **Baris D35** | `AO`→**S**, `VCC`→**V**, `GND`→**G** | ADC1_CH7 (GPIO 35) | Sensor kelembapan resistif komparasi |
| **Modul Relay Songle SRD-05VDC** | **Baris D2 [S]**<br>Header Daya **5V** & **GND** | `IN`→**D2 [S]**<br>`VCC`→**5V (Daya Shield)**<br>`GND`→**GND Shield** | Digital Output (GPIO 2, Active-LOW) | Saklar pengaman Solenoid Valve NC AC 220V |

---

### Analisis Mendalam Modul Relay 1 Channel Songle SRD-05VDC-SL-C:
1. **Sisi Tegangan Kontrol (Sisi Input ESP32)**:
   - **`VCC`**: Hubungkan ke pin **5V** pada header daya shield (Koil relay butuh 5V DC agar tarikan medan magnetik bertenaga). ⚠️ **JANGAN hubungkan VCC ke GND!**
   - **`GND`**: Hubungkan ke pin **GND** mandiri pada shield (Ground referensi bersama).
   - **`IN`**: Hubungkan ke pin **D2 [S]** (Signal GPIO 2 ESP32). Modul bertipe **Active-LOW** (Relay ON saat sinyal LOW/0V, Standby OFF saat sinyal HIGH/3.3V).
2. **Sisi Terminal Beban Screw (Output ke Solenoid Valve NC AC 220V)**:
   - **常开 (Cháng Kāi / NO = Normally Open)**: Hubungkan ke salah satu kabel Solenoid Valve.
   - **公共端 (Gōnggòng Duān / COM = Common)**: Hubungkan ke kabel Fasa (Live/L) dari sumber listrik PLN AC 220V.
   - **常闭 (Cháng Bì / NC = Normally Closed)**: Dibiarkan kosong.
   - Kabel Netral (N) PLN AC 220V langsung terhubung ke kabel satunya lagi dari Solenoid Valve.
3. **Mekanisme Kerja Solenoid Valve Plastik Normally Closed (NC) AC 220V**:
   - **Kondisi Standby (Relay OFF / NO Terbuka)**: Solenoid tidak menerima tegangan 220V → Katup tertutup rapat secara mekanis (air tidak mengalir). Sangat aman jika terjadi mati lampu / putus sinyal.
   - **Kondisi Aktif (Relay ON / NO Terhubung ke COM)**: Arus AC 220V mengalir ke kumparan solenoid → Katup membuka dengan bantuan tekanan air → Air irigasi mengalir.
   - **Tipe Bertekanan**: Memerlukan tekanan air minimal dari pipa/pompa/toren tinggi agar diafragma katup dapat membuka sempurna.

---

## 3. ALUR PEMBUATAN TAMAN BARU DI DASHBOARD

Tahapan tambah taman sekarang telah **100% Wireless** tanpa memerlukan setting port COM / kabel USB laptop:

1. Masuk ke halaman **Taman Saya** (`/dashboard`).
2. Klik tombol **Tambah Taman**.
3. **Langkah 1**: Masukkan **Nama Taman** (misal: *Greenhouse Tomat A*), tipe lahan (Jagung, Greenhouse, Sawah, dsb), dan lokasi.
4. **Langkah 2 & 3**: Pilih sensor yang dipasang (Kelembapan, Suhu, pH, EC).
5. **Langkah 4 (Sistem Wireless)**: Pilih board **ESP32**. Sistem otomatis mengarahkan ke mode transmisi **Wireless WiFi Stream**.
6. **Langkah 5 (Review & Simpan)**: Klik **Simpan & Dapatkan Token**.
7. Sistem langsung otomatis menerbitkan **Device Claim Token** unik (contoh: `NTX-8F2KD9A1LX4M`) dan membuka dashboard taman Anda.

---

## 4. LANGKAH PAIRING ESP32 TANPA KABEL (WIRELESS PORTAL)

Setelah firmware ter-upload ke ESP32, pairing dilakukan sekali saja melalui HP / Laptop:

### Langkah 1: Nyalakan ESP32
- Hubungkan ESP32 ke adaptor charger HP 5V menggunakan kabel USB.
- Jika ESP32 belum terhubung ke WiFi, LED akan berkedip dan hotspot darurat aktif.

### Langkah 2: Hubungkan ke Hotspot ESP32
- Buka menu **Pengaturan Wi-Fi** di HP atau Laptop Anda.
- Sambungkan ke jaringan Wi-Fi:
  - **Nama SSID**: `NUTRIX-ESP32-PAIR`
  - **Password**: *(Kosong / Tanpa Password)*

### Langkah 3: Isi Formulir Captive Portal
- Begitu tersambung, jendela browser konfigurasi akan **otomatis muncul**.
  *(Jika tidak muncul otomatis, buka Chrome/Safari dan ketik alamat IP `192.168.4.1`)*.
- Klik menu **Configure WiFi**.
- Pilih nama WiFi rumah/kebun Anda (wajib frekuensi 2.4 GHz).
- Masukkan password WiFi Anda.
- Pada kolom **Device Token (from Web)**: Tempelkan (paste) Token yang Anda salin dari dashboard Nutrix.
- Klik tombol **Save**.

### Langkah 4: Selesai!
- ESP32 akan menyimpan token ke memori internal flash (NVS) secara permanen.
- ESP32 me-restart dirinya sendiri dan tersambung ke cloud Railway.
- Dalam hitungan detik, indikator di dashboard Nutrix Anda akan langsung berubah menjadi **IOT SENSOR ONLINE (LIVE)**.

---

## 5. FITUR RESET TOTAL DASHBOARD & GANTI TOKEN

Jika Anda ingin membersihkan data simulasi/testing, mengganti perangkat fisik ESP32, atau memindahkan token ke kebun lain:

1. Buka dashboard taman Anda di web.
2. Di bagian header kanan atas, klik tombol merah: **Reset Total Dashboard**.
3. Konfirmasi jendela peringatan:
   - Seluruh data telemetri historis akan **dihapus bersih**.
   - Status koneksi dikembalikan ke **MENUNGGU PAIRING (OFFLINE)**.
   - **Token Pairing Baru** diterbitkan secara otomatis.
4. Modal pairing akan langsung terbuka dan menampilkan token baru Anda yang siap dimasukkan ke ESP32.

---

## 6. OTOMATISASI KERAN (RELAY) & INDIKATOR LED

### Logika Keputusan Otomatis (Cloud AI & Firmware):
1. **Kelembapan Tanah < 30% (Kering Kritis)**:
   - Server Railway merespon paket telemetri dengan instruksi `water_valve: "ON"` selama 10 detik.
   - ESP32 mengaktifkan **Relay GPIO 2 (Shield D2 [S])** untuk membuka Solenoid Valve NC.
   - Status relay berubah menjadi ON dan aktivitas otomatis tercatat di tab riwayat dashboard pengguna.

2. **Kelembapan Tanah ≥ 30% (Optimal)**:
   - Solenoid Valve tetap dalam posisi STANDBY (tertutup rapat tanpa konsumsi listrik koil).

3. **Indikator Aktuasi & Relay**:
   - Modul Relay Songle dilengkapi LED indikator fisik di modulnya yang menyala saat relay terpicu aktif (LOW).
   - ESP32 otomatis mematikan relay kembali setelah durasi irigasi selesai.

---

## 7. TROUBLESHOOTING & SOLUSI KENDALA

| Gejala | Kemungkinan Penyebab | Solusi |
|---|---|---|
| **Hotspot `NUTRIX-ESP32-PAIR` tidak muncul** | ESP32 masih menyimpan data WiFi lama | Tekan tombol RST pada ESP32, atau dekatkan HP ke modul ESP32. |
| **Portal `192.168.4.1` tidak bisa dibuka** | HP menggunakan mobile data | Matikan data seluler di HP Anda sementara waktu saat terhubung ke hotspot ESP32. |
| **Status di Dashboard tetap OFFLINE** | Token tidak cocok atau salah ketik | Klik tombol *Reset Total Dashboard* di web, salin token baru, dan ulangi konfigurasi WiFi di ESP32. |
| **Error SSL / HTTPS di Serial Monitor** | Waktu internal ESP32 belum sinkron | Firmware Nutrix sudah dilengkapi `setInsecure()` otomatis untuk bypass validasi sertifikat self-signed pada dev endpoint. |
| **Nilai sensor kelembapan selalu 0% atau 100%** | Sensor belum tercelup atau kabel jumper longgar | Periksa sambungan kabel sensor ke Pin 34, 3V3, dan GND. Kalibrasi batas basah/kering di bagian `map()` firmware jika perlu. |
