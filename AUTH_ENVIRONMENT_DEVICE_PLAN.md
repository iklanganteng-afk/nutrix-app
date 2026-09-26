# Rencana: Auth, Environment, Device, dan Telemetry NUTRIX

## Tujuan Pengalaman Pengguna

NUTRIX memakai alur seperti wallet + dashboard IoT:

```text
Pengunjung → Sign in / Sign up → Environment → Add device → Telemetry live
```

Saat belum masuk, pengguna tidak melihat angka telemetry demo. Dashboard menampilkan state onboarding yang menjelaskan langkah berikutnya. Setelah masuk, pengguna dapat membuat environment dan mendaftarkan beberapa device sensor di dalamnya.

## Aturan Utama

- **Belum sign in:** telemetry, remote action, export, dan add environment terkunci.
- **Sudah sign in, belum ada environment:** tampilkan empty state + tombol **Add Environment**.
- **Environment ada, belum ada device:** tampilkan empty state + tombol **Add Device**.
- **Device aktif tersedia:** tampilkan telemetry dan action berdasarkan environment/device yang dipilih.
- Satu environment dapat memiliki banyak device; setiap device memiliki status koneksi serta metrik sensor sendiri.

## Tahap 1 — Perbaikan Sign In, Sign Up, Profile, dan Settings

### 1. Auth state yang jelas

- Simpan session demo pengguna di `localStorage` agar tetap masuk setelah refresh.
- Validasi form sederhana: nama, email, password; tampilkan pesan error di field yang bermasalah.
- Sign in dan sign up mengarahkan ke dashboard onboarding, bukan langsung menampilkan data telemetry.
- Log out menghapus session aktif lalu mengembalikan dashboard ke state publik.

### 2. Profile / Account Settings

- Profile menampilkan nama, email, address, Energy Credits, jumlah environment, dan jumlah device.
- User dapat mengubah nama tampilan serta avatar/identicon seed dari Settings.
- Profil menampilkan nama, email, preferensi bahasa, dan tema.

### 3. Settings

- Tab **Account**: nama, email, avatar seed.
- Tab **Notifications**: aktif/nonaktifkan alert moisture, pH, EC, serta notifikasi aksi.
- Tab **Data**: reset demo data dengan konfirmasi eksplisit.
- Pengaturan disimpan per pengguna di browser untuk versi frontend demo.

## Tahap 2 — Environment Management

### 4. Environment list

- Ganti pilihan environment statis di navbar menjadi daftar milik pengguna.
- Tambahkan tombol **Add Environment** di dropdown/panel environment setelah sign in.
- Environment memiliki: nama, tipe lahan (Open Field/Greenhouse/Rice Paddy), lokasi singkat, dan warna indikator.
- Environment dapat diubah nama, dipilih aktif, atau dihapus dengan konfirmasi.

### 5. Modal Add Environment

Form:

- Environment name
- Field type
- Location / zone
- Optional description

Setelah dibuat, environment menjadi aktif dan mengarah ke state **Add Device**.

## Tahap 3 — Device & Multi-Sensor

### 6. Device model

Satu environment memiliki daftar device. Struktur frontend demo:

```text
Environment
  └─ Device (Sensor ID, name, status, last sync)
       └─ Sensors (pH, moisture, temperature, EC)
```

Contoh: `Sensor Greenhouse-01` dapat memiliki pH, kelembapan, suhu, dan EC; device lain dapat hanya memiliki kelembapan + suhu.

### 7. Add Device flow

- Dapat diakses hanya setelah sign in dan environment aktif.
- Pilihan metode: **Scan QR** (simulasi) atau **Manual Sensor ID**.
- Form: device name, Sensor ID, lokasi/bed, dan pilihan sensor.
- Pilihan sensor dibuat sebagai checkbox/card yang dapat dipilih lebih dari satu:
  - **Sensor Suhu** — temperature tanah/udara.
  - **Sensor pH** — tingkat keasaman tanah.
  - **Sensor EC** — electrical conductivity/kepadatan nutrisi.
  - **Sensor Kelembapan** — moisture tanah.
- Minimal satu sensor wajib dipilih. Pilihan sensor disimpan pada device dan dapat diedit dari detail device.
- Setelah ditambahkan, simulasi pairing/connection lalu device berubah menjadi **Online**.

### 8. Device switcher

- Tambahkan panel/list device di dashboard atau sidebar.
- Pilihan device memfilter metrik telemetry, Activity Log, dan remote action ke device tersebut.
- Kartu telemetry bersifat dinamis: hanya sensor yang dipilih ketika menambah device yang akan muncul. Misalnya device dengan Suhu + Kelembapan hanya menampilkan dua kartu itu, bukan pH dan EC kosong.
- Status: Online, Syncing, Offline, atau Needs setup.

## Tahap 4 — Dashboard State dan Telemetry

### 9. Public dashboard / signed-out state

Sebelum sign in:

- Sembunyikan nilai pH, moisture, temperature, EC, Health Score, dan quick actions.
- Tampilkan panel onboarding dengan tombol **Sign in to connect your farm**.
- Hero dan desain utama tetap terlihat, jadi landing page tidak kosong.

### 10. Empty states

- **No environment:** “Create your first farm environment.”
- **No device:** “Add a sensor device to start receiving telemetry.”
- **Device offline:** telemetry terakhir ditampilkan redup bersama waktu last sync dan tombol reconnect.

### 11. Live telemetry

- Simulasi berjalan hanya untuk device yang berstatus online dan hanya menghasilkan data untuk sensor yang dipilih pada device tersebut.
- Data setiap device disimpan terpisah dan mempertahankan riwayat sparkline.
- Health Score dihitung dari sensor device aktif; bila environment memiliki beberapa device, tampilkan ringkasan/average di tahap berikutnya.

## Tahap 5 — Data dan Keamanan Demo

### 12. Penyimpanan browser

Key data per user:

- User profile & settings
- Environments
- Devices dan sensor terpilih
- Telemetry history
- Activity dan notifications

### 13. Reset dan seed demo

- User baru memulai tanpa environment dan tanpa device.
- Sediakan opsi **Load sample farm** di onboarding agar presentasi/demonstrasi tetap cepat.
- Reset demo harus menghapus data milik pengguna aktif saja dan meminta konfirmasi.

## Urutan Implementasi Rekomendasi

1. Auth session + public/signed-out dashboard state
2. Profile dan Settings yang benar-benar menyimpan data
3. Environment CRUD (buat, pilih, edit, hapus)
4. Device CRUD + pairing simulasi
5. Empty states dan device switcher
6. Telemetry per device + Activity/notification terhubung
7. Load sample farm + reset demo

## Batas Versi Pertama

- Tetap frontend-only menggunakan `localStorage`; tidak ada backend/password sungguhan dahulu.
- QR scan adalah simulasi UI, bukan akses kamera.
- Remote action hanya aktif jika user masuk dan device online.
- Desain memakai palet, tipografi, dan modal NUTRIX yang sudah ada; tidak mengubah hero atau struktur visual utama.
