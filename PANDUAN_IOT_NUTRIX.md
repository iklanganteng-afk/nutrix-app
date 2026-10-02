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
│  [Sensor Kelembapan GPIO 34] ──┐                            │
│                                 ▼                           │
│                      [ESP32 Microcontroller]                │
│                                 │ (WiFi 2.4 GHz HTTPS)      │
│  [Relay Keran Air GPIO 26] ◄────┘                           │
└─────────────────────────────────┼───────────────────────────┘
                                  │
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

## 2. SPESIFIKASI HARDWARE & PINOUT ESP32

Firmware: `iot_firmware/nutrix_esp32_firmware.ino`

| Komponen | Pin ESP32 | Tipe Pin | Fungsi |
|---|---|---|---|
| **Sensor Kelembapan Tanah** | **GPIO 34** | ADC1 (Input Analog) | Membaca kadar air tanah (0 - 100%) |
| **Modul Relay Keran / Pompa** | **GPIO 26** | Digital Output (Active-HIGH) | Mengaktifkan solenoid valve keran air |
| **Buzzer Aktif 5V** | **GPIO 27** | Digital Output | Alarm saat tanah kering kritis |
| **LED Indikator Onboard** | **GPIO 2** | Digital Output (Blue LED) | Status jaringan WiFi & transmisi data |

> **Catatan Port Daya**: ESP32 cukup dicolokkan ke adaptor charger HP 5V / USB biasa. Tidak memerlukan koneksi kabel ke laptop setelah firmware di-upload.

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
   - ESP32 mengaktifkan **Relay GPIO 26** untuk membuka keran solenoid irigasi.
   - Buzzer GPIO 27 berbunyi 2 kali pendek sebagai notifikasi.
   - Aktivitas otomatis tercatat di tab riwayat dashboard pengguna.

2. **Kelembapan Tanah ≥ 30% (Optimal)**:
   - Keran tetap dalam posisi STANDBY (tertutup).

3. **Indikator LED Status (GPIO 2)**:
   - **Berkedip cepat**: ESP32 sedang dalam mode konfigurasi Access Point (`NUTRIX-ESP32-PAIR`).
   - **Menyala redup / Berkedip 1x tiap 5 detik**: ESP32 berhasil mengirimkan paket telemetri HTTPS ke server Railway.

---

## 7. TROUBLESHOOTING & SOLUSI KENDALA

| Gejala | Kemungkinan Penyebab | Solusi |
|---|---|---|
| **Hotspot `NUTRIX-ESP32-PAIR` tidak muncul** | ESP32 masih menyimpan data WiFi lama | Tekan tombol RST pada ESP32, atau dekatkan HP ke modul ESP32. |
| **Portal `192.168.4.1` tidak bisa dibuka** | HP menggunakan mobile data | Matikan data seluler di HP Anda sementara waktu saat terhubung ke hotspot ESP32. |
| **Status di Dashboard tetap OFFLINE** | Token tidak cocok atau salah ketik | Klik tombol *Reset Total Dashboard* di web, salin token baru, dan ulangi konfigurasi WiFi di ESP32. |
| **Error SSL / HTTPS di Serial Monitor** | Waktu internal ESP32 belum sinkron | Firmware Nutrix sudah dilengkapi `setInsecure()` otomatis untuk bypass validasi sertifikat self-signed pada dev endpoint. |
| **Nilai sensor kelembapan selalu 0% atau 100%** | Sensor belum tercelup atau kabel jumper longgar | Periksa sambungan kabel sensor ke Pin 34, 3V3, dan GND. Kalibrasi batas basah/kering di bagian `map()` firmware jika perlu. |
