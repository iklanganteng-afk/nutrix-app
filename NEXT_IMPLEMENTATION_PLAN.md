# Roadmap Lanjutan NUTRIX — Smart Agriculture Workspace

Prinsip utama: mempertahankan tata letak, tipografi, dan palet hijau-obsidian NUTRIX yang sudah ada. Fitur baru ditambahkan sebagai panel, modal, atau state interaktif agar tampilan inti tidak berubah.

## Tahap 1 — Profile & Sensor Experience

### 1. Account Detail
- **Status: completed (frontend demo).**
- Klik avatar membuka informasi akun: nama, email, bahasa, tema, dan preferensi notifikasi.

### 2. Import / Connect Existing Sensor
- Modal **Connect Sensor** diberi dua jalur: *scan QR* atau masukkan Sensor ID.
- Validasi visual singkat, lalu sensor muncul pada daftar sensor taman.
- Tidak perlu backend dahulu; gunakan penyimpanan browser agar demo tetap terasa nyata.

### 3. Network Switch Loading
- **Status: completed (skeleton + environment persistence).**
- Saat berpindah tipe taman, tampilkan skeleton telemetry dan status *“Fetching sensor data”*.
- Simpan environment terakhir yang dipilih agar tetap aktif saat halaman dibuka ulang.

## Tahap 2 — Transaction Experience

### 4. Transaction Detail View
- **Status: completed (frontend demo).**
- Setiap item Activity dapat diklik untuk membuka detail aksi: taman tujuan, waktu, durasi, dan status.
- Status pending berubah otomatis menjadi confirmed dalam simulasi.

### 5. Notification Center
- **Status: completed (frontend demo).**
- Ikon lonceng di navbar dengan badge jumlah notifikasi.
- Isinya peringatan kelembapan rendah, koneksi sensor putus, sinkronisasi selesai, dan aksi berhasil/gagal.
- Sediakan tombol *Mark all as read*.

### 6. Action Guardrails
- Untuk Water Pump dan Biofertilizer, tampilkan estimasi dampak: volume, penggunaan energi, dan batas keamanan.
- Tambahkan konfirmasi ekstra untuk durasi panjang agar remote action tidak mudah salah eksekusi.

## Tahap 3 — Smart Agriculture Intelligence

### 7. Farm Portfolio
- Ubah Health Score menjadi ringkasan portofolio beberapa lahan: luas area, skor kesehatan, estimasi hasil, dan perubahan harian.
- Klik satu lahan untuk memfilter telemetry serta Activity Log.

### 8. AI Insight Timeline
- Riwayat rekomendasi AI per hari: masalah terdeteksi, tindakan yang dijalankan, dan hasilnya.
- Tetap gunakan popup izin akses AI seperti koneksi DApp yang sudah ada.

### 9. Alert Rules
- Pengaturan ambang batas pH, kelembapan, suhu, dan EC dari modal Settings.
- Jika nilai sensor melewati ambang, buat Activity otomatis dan notifikasi di notification center.

## Tahap 4 — Finishing & Production Readiness

### 10. Persistent Demo Data
- **Status: completed for Activity, notifications, and selected environment.**
- Simpan hanya preferensi bahasa, tema, dan pengaturan UI non-sensitif di `localStorage`.
- Tambahkan tombol reset demo secara eksplisit di Settings.

### 11. Accessibility & Mobile Polish
- Fokus keyboard yang jelas, penutupan modal dengan Escape, dan ukuran sentuh yang nyaman di ponsel.
- Pastikan dropdown dan modal tidak terpotong pada layar kecil.

### 12. Backend Integration (opsional)
- API Laravel untuk telemetry, remote action, dan Activity Log.
- WebSocket/polling untuk data sensor real-time.
- Role pengguna: admin dapat menjalankan aksi, viewer hanya dapat memantau.

## Urutan Rekomendasi

1. Transaction Detail View + Notification Center
2. Account Detail + Connect Existing Sensor
3. Persistent Demo Data + Alert Rules
4. Farm Portfolio + AI Insight Timeline
5. Integrasi backend real-time

Tahap 1–4 dapat dibuat sepenuhnya di frontend terlebih dahulu dan tidak mengubah desain utama dashboard.
