# NUTRIX — Implementation Plan Pengguna Biasa

Dokumen ini menjadi acuan pengalaman **pengguna biasa (farmer/agronomist)** setelah Sign In. Jalur ini sengaja berbeda dari `/admin`: fokusnya adalah mengelola lahan sendiri, membaca kondisi tanaman, dan mengambil aksi sederhana dengan aman.

---

## 1. Batas Produk dan Alur Masuk

| Peran | Redirect setelah OTP | Pengalaman utama |
|---|---|---|
| `user` | `/dashboard` | Workspace pribadi untuk kebun miliknya sendiri |
| `admin` | `/admin` | Master Control Center untuk seluruh platform |

Aturan utama:

- User hanya dapat melihat, membuat, dan menghapus taman yang memiliki `user_id` miliknya.
- Dashboard user tidak menampilkan daftar akun, SMTP, log keamanan global, atau data taman pengguna lain.
- Aksi remote pada taman hanya terlihat pada detail taman tersebut.
- Tema dan bahasa memakai preferensi global yang sudah tersimpan di browser.

---

## 2. Arah Visual

Nama pengalaman: **My Farm Workspace**.

Karakter visual:

- Lebih hangat, lapang, dan personal daripada Admin Command Center.
- Tetap memakai font `Cinzel` untuk judul dan `Outfit` untuk isi.
- Tema warna mengikuti pilihan user: Emerald, Harvest, Nordic, Obsidian, Midnight, atau Hydro.
- Dominan kartu bento, ilustrasi kebun, indikator kondisi yang mudah dibaca, dan copy yang ramah.
- Hijau berarti sehat, amber berarti perlu perhatian, merah berarti tindakan diperlukan.
- Hindari tabel teknis padat kecuali di riwayat aktivitas atau ekspor data.

Wireframe desktop:

```text
┌──────────────────────────────────────────────────────────────────────┐
│ NUTRIX   [Tema] [Bahasa]                    [Notif] [Profil user]   │
├──────────────────────────────────────────────────────────────────────┤
│ Selamat datang, Nama User                    [+ Tambah Taman]        │
│ Pantau kondisi kebunmu dan ambil tindakan saat diperlukan.           │
├──────────────────────────────┬───────────────────────────────────────┤
│ Ringkasan kebun              │ Insight hari ini                      │
│ • 3 taman aktif              │ “Kelembapan Kebun Utara mulai turun”  │
│ • 2 kondisi optimal          │ [Buka taman]                          │
│ • 1 perlu perhatian          │                                       │
├──────────────────────────────────────────────────────────────────────┤
│ Kartu taman: [foto/ikon] nama · tipe · lokasi · health · Buka →     │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 3. Halaman dan State

### A. `/dashboard` — My Farm Workspace

#### State guest

- Hero ringkas: “Masuk untuk mulai memantau taman kamu.”
- Tombol Sign In dan Sign Up membuka modal OTP.
- Tidak ada data contoh, sensor contoh, maupun activity user lain.

#### State setup — user sudah login, belum punya taman

- Empty state dengan ilustrasi kebun kosong.
- Headline: “Tambahkan taman pertama kamu.”
- CTA utama: `+ Tambah Taman`.
- Modal menanyakan nama, tipe ekosistem, dan lokasi opsional.

#### State ready — user punya taman

- Sapaan personal dan ringkasan total taman.
- Kartu per taman berisi:
  - Nama dan lokasi.
  - Tipe: corn / greenhouse / rice / custom.
  - Health score dan label status.
  - Sensor terakhir diperbarui.
  - CTA `Buka Dashboard` dan menu hapus dengan konfirmasi.
- Insight singkat tidak boleh menjanjikan AI sungguhan sebelum backend telemetry tersedia; gunakan label “simulasi” bila masih frontend demo.

### B. `/taman/{taman}` — Detail Farm Monitor

Header:

- Tombol kembali ke `Taman Saya`.
- Nama taman, tipe, lokasi, dan indikator koneksi sensor.
- Menu titik tiga: edit taman, hapus taman, ekspor data.

Konten utama:

1. Empat kartu sensor: pH, kelembapan, suhu, EC.
2. Health score dengan alasan status yang mudah dimengerti.
3. Rekomendasi: misalnya “Kelembapan berada 8% di bawah target.”
4. Quick actions: Sync, Siram, Pupuk, Activity, Export.
5. Timeline aktivitas khusus taman ini.

Wireframe:

```text
← Taman Saya       Kebun Jagung Utara · 0xABCD...12 · Live

[ pH 6.6 ] [ Moisture 58% ] [ Temperature 27.3°C ] [ EC 1.42 ]

             Overall Farm Health: 89 / Optimal

[Sync]  [Siram]  [Pupuk]  [Aktivitas]  [Export]

Insight hari ini                         Riwayat aktivitas
Kelembapan menurun perlahan.             10:05  Sync selesai
Pertimbangkan penyiraman 30 detik.       09:30  Pompa dijalankan
```

---

## 4. Interaksi yang Harus Berfungsi

| Komponen | Perilaku yang diharapkan |
|---|---|
| Tema dan bahasa | Tersimpan di `localStorage`, diterapkan sebelum render, konsisten di welcome/dashboard/detail. |
| Tambah taman | Validasi form, simpan ke DB, lalu redirect ke halaman detail taman baru. |
| Hapus taman | Konfirmasi eksplisit, hanya pemilik yang dapat menghapus, kembali ke dashboard. |
| Sensor | Fase demo: beri label simulasi. Fase production: baca record telemetry terbaru dari server. |
| Sync | Fase demo menampilkan toast + activity lokal. Production memanggil endpoint dan mencatat hasil. |
| Siram/Pupuk | Tampilkan dampak dan konfirmasi; jangan kirim aksi remote tanpa persetujuan user. |
| Aktivitas | Data harus dipisah per `taman_id`, bukan satu `localStorage` global. |
| Export | CSV hanya berisi telemetry taman pemilik yang sedang dibuka. |
| Modal | Bisa ditutup oleh tombol close, klik overlay, dan tombol `Escape`; scroll body selalu dipulihkan. |

---

## 5. Urutan Implementasi

### Fase 1 — Stabilkan pengalaman yang sudah ada

1. Pastikan database runtime tersambung sebelum uji browser end-to-end.
2. Pertahankan satu sumber kebenaran login: session Laravel; `localStorage` hanya untuk tema, bahasa, dan preferensi non-sensitif.
3. Uji OTP register, login, resend, expired, logout, dan redirect role.
4. Audit modal, dropdown, dan navigasi agar tidak ada library JavaScript yang termuat dua kali.

Kriteria selesai: akun baru bisa daftar, memverifikasi OTP, login, logout, dan melihat state taman kosong tanpa data demo.

### Fase 2 — My Farm Workspace

1. Rapikan kartu taman dan empty state sesuai desain di atas.
2. Tambahkan edit taman (`PATCH /taman/{taman}`) bila dibutuhkan.
3. Tambahkan notifikasi dan activity yang di-scope per user/taman.
4. Tambahkan halaman profil user ringan: nama, email, dan preferensi.

Kriteria selesai: user dapat mengelola taman pribadinya dari awal sampai akhir secara konsisten di desktop dan mobile.

### Fase 3 — Telemetry Persisten

Tambahkan tabel `sensor_telemetries`:

```text
id, taman_id, ph, moisture, temperature, ec, health_score, recorded_at
```

Tambahkan tabel `farm_activities`:

```text
id, taman_id, user_id, type, title, detail, status, metadata, created_at
```

Endpoint minimum:

```text
GET  /api/taman/{taman}/telemetry
POST /api/taman/{taman}/sync
POST /api/taman/{taman}/actions/water
POST /api/taman/{taman}/actions/fertilize
GET  /api/taman/{taman}/export.csv
```

Kriteria selesai: reload halaman tidak menghilangkan riwayat dan tidak mencampur data antar-taman/antar-akun.

### Fase 4 — Real-time dan keamanan aksi

1. Polling ringan atau WebSocket untuk telemetry terbaru.
2. Alert rule per taman: batas pH, moisture, suhu, EC.
3. Konfirmasi aksi remote dengan durasi, estimasi dampak, dan status eksekusi.
4. Rate limit endpoint aksi dan audit trail server-side.

Kriteria selesai: user menerima alert yang relevan dan setiap aksi dapat ditelusuri dengan aman.

---

## 6. Hal yang Tidak Boleh Dilakukan

- Jangan tampilkan data dashboard admin pada user biasa.
- Jangan gunakan demo sensor/data hardcoded sebagai state default user baru.
- Jangan simpan status login atau data aksi sensitif di `localStorage`.
- Jangan tampilkan “live” bila data masih hasil simulasi tanpa label.
- Jangan izinkan user mengakses `/taman/{id}` milik pengguna lain; tetap gunakan validasi ownership di controller.

---

## 7. Checklist Uji Akhir

- [ ] User baru: daftar → OTP → dashboard kosong.
- [ ] User membuat taman: tersimpan dan redirect ke detail taman.
- [ ] User A tidak bisa melihat/menghapus taman User B.
- [ ] Tema dan bahasa sama setelah berpindah welcome → dashboard → detail taman.
- [ ] Semua modal dapat ditutup dan body tidak terkunci scroll.
- [ ] Mobile: kartu, dropdown, dan quick action tetap mudah disentuh.
- [ ] Admin login tetap diarahkan ke `/admin`; user login tetap ke `/dashboard`.
- [ ] Export, activity, alert, dan telemetry hanya memakai data taman aktif milik user.
