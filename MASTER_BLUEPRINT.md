# 🌿 NUTRIX — Master Blueprint & Step-by-Step Implementation Roadmap

Dokumen ini disusun sebagai **panduan arsitektur dan eksekusi komprehensif** untuk melanjutkan pengembangan website **NUTRIX Smart Farming**. Alur dirancang agar **sinkron, modular, bersih dari data ghost/dummy liar, dan mudah dieksekusi secara bertahap**.

---

## 1. Arsitektur & Alur Website (User Flow)

```mermaid
graph TD
    A["Landing Page Terang (welcome.blade.php)"] -->|Klik 'Mulai Pantau' / 'Jelajahi Solusi'| B["Dashboard / Hub (/dashboard)"]
    
    subgraph Sesi Pengguna
        B -->|Belum Login (Guest)| C["State: Public<br>Tampil Tombol 'Sign In to Continue'<br>Modal Auth (Sign In / Sign Up)"]
        C -->|POST /register atau POST /login| D["Autentikasi Valid & Session Regenerate"]
        D -->|Redirect ke /dashboard| E["User Login Valid"]
        
        E -->|Belum Ada Taman (0 Taman)| F["State: Setup (Empty State Bersih)<br>Pesan: 'Tambahkan taman pertama kamu'<br>Tombol 'Tambah Taman'"]
        F -->|POST /taman| G["Taman Tersimpan di Database"]
        
        E -->|Sudah Memiliki Taman| H["State: Ready (List Taman User)<br>Kartu Bento per Taman<br>Tombol Buka Dashboard & Hapus"]
        H -->|Klik 'Buka Dashboard' (/taman/{id})| I["Halaman Monitoring Taman (show.blade.php)<br>Telemetri 4 Sensor Sesuai Tipe Lahan<br>Overall Farm Health<br>Remote Actions & History"]
        
        H -->|Klik Logout (/logout)| J["Session Hancur (Invalidate) & Kembali Bersih ke /"]
    end
```

### Aturan Alur (Non-Negotiable):
1. **Akun Baru Harus 0 Data (Mulai dari Nol):**
   - Tidak boleh ada hardcoded sensor seperti *"NUTRIX Sensor 01"* atau data bawaan yang tiba-tiba muncul.
   - Database menjadi satu-satunya sumber kebenaran (*single source of truth*). Jika `tamans` milik user kosong, halaman **wajib** menampilkan tampilan setup (tambah taman baru).
2. **Isolasi Data per Akun:**
   - User A hanya bisa melihat dan mengelola taman milik User A (`where user_id = Auth::id()`).
   - Sesi pergantian akun via Sign Out akan membersihkan session PHP dan state di browser tanpa meninggalkan sisa di `localStorage`.
3. **Scroll & Layar:**
   - Semua halaman harus dapat di-scroll dengan wajar tanpa tertahan oleh class modal overlay yang bocor atau CSS `overflow: hidden`.

---

## 2. Struktur Database & Model Relasi

### A. Tabel `users`
- `id` (BIGINT, PK)
- `name` (VARCHAR)
- `email` (VARCHAR, Unique)
- `password` (VARCHAR, Hashed bcrypt)
- `created_at`, `updated_at`

### B. Tabel `tamans`
- `id` (BIGINT, PK)
- `user_id` (BIGINT, FK -> `users.id`, onDelete Cascade)
- `name` (VARCHAR - Nama taman, cth: "Kebun Jagung Blok B")
- `type` (ENUM: `'corn'`, `'greenhouse'`, `'rice'`, `'custom'`)
- `location` (VARCHAR, Nullable)
- `created_at`, `updated_at`

### C. Tabel `sensor_telemetries` (Opsional untuk Fase Persistence)
- `id` (BIGINT, PK)
- `taman_id` (BIGINT, FK -> `tamans.id`, onDelete Cascade)
- `ph` (DECIMAL 3,1)
- `moisture` (INT)
- `temperature` (DECIMAL 4,1)
- `ec` (DECIMAL 4,2)
- `health_score` (INT)
- `recorded_at` (TIMESTAMP)

---

## 3. Analisis & Solusi Bug Saat Ini

### Masalah 1: "Habis pencet Mulai Pantau, halaman tidak bisa di-scroll"
- **Penyebab:**
  1. Tombol `Mulai Pantau` di [welcome.blade.php](file:///c:/xampp/htdocs/NUTRIX/resources/views/welcome.blade.php#L279) mengarah ke `/login`, yang kemudian oleh [web.php](file:///c:/xampp/htdocs/NUTRIX/routes/web.php#L21-L23) di-redirect ke `/dashboard` (`taman.index`).
  2. Di `taman.index`, elemen `<section class="dashboard-section container">` mewarisi style dari `style.css`:
     ```css
     .dashboard-section {
         margin-top: -20vh; /* Dirancang untuk overlap hero di landingpage lama! */
     }
     ```
     Ketika dibuka di `taman.index` (yang tidak memiliki hero section 250vh), `margin-top: -20vh` menarik konten ke atas di balik navbar. Ditambah padding-top dan ketiadaan tinggi minimum, halaman tampak kaku dan terpotong.
  3. Selain itu, jika modal terbuka tanpa interaksi yang menutupnya, overlay dapat memblokir interaksi pointer.
- **Solusi Tepat:**
  - Tambahkan kelas spesifik untuk halaman mandiri seperti `.standalone-dashboard { margin-top: 0 !important; min-height: 80vh; }` pada `taman/index.blade.php`.
  - Pastikan `html, body` tidak memiliki `height: 100%; overflow: hidden;`.

### Masalah 2: "Akun harusnya 0 tapi masih muncul data sensor lama"
- **Penyebab:**
  - Di `script.js` versi lama, terdapat data demo statis di `localStorage` (`nutrixEnvironment`, `nutrixAccounts`, dll) atau hardcoded markup di cache view view blade lama (`storage/framework/views`).
- **Solusi Tepat:**
  - Semua data akun dan taman diambil **murni dari variabel server Blade**:
    `window.NUTRIX_USER = @auth {!! json_encode(...) !!} @else null @endauth;`
  - Bersihkan cache view Laravel: `php artisan view:clear` & `php artisan config:clear`.
   - Hapus referensi `localStorage` yang menyimpan nama sensor default.

---

## 4. Roadmap Langkah Demi Langkah (Step-by-Step Execution Plan)

### 📌 FASE 1: Perbaikan Navigasi & Tampilan Dashboard Mandiri
1. **Rapikan Layout `resources/views/taman/index.blade.php`:**
   - Bungkus `<section class="dashboard-section container standalone-dashboard">` dengan CSS:
     ```css
     .standalone-dashboard {
         margin-top: 0 !important;
         min-height: calc(100vh - 180px);
         margin-bottom: 60px;
     }
     ```
   - Pastikan navbar brand mengarah ke `/` (welcome page).
   - Pastikan tombol "Mulai Pantau" di [welcome.blade.php](file:///c:/xampp/htdocs/NUTRIX/resources/views/welcome.blade.php) langsung menuju `/dashboard`.

2. **Validasi State Tampilan (Guest vs Setup vs Ready):**
   - **Guest (belum login):** Menampilkan banner ramah *"Masuk untuk mulai memantau"* + tombol *"Sign In"*. Begitu diklik, modal auth muncul.
   - **Setup (login, taman = 0):** Menampilkan icon kebun kosong + tombol *"Tambah Taman"*. Tidak boleh ada kartu sensor bawaan!
   - **Ready (login, taman > 0):** Menampilkan grid bento box taman milik user tersebut saja.

---

### 📌 FASE 2: Sinkronisasi Form Autentikasi (Sign In & Sign Up)
1. **Modal Form di `resources/views/taman/index.blade.php` & `layouts/app.blade.php`:**
   - Sign Up Form memiliki:
     - `name` (Username)
     - `email` (Email unik)
     - `password` (Password min 8 karakter)
     - `password_confirmation` (Konfirmasi password)
   - Sign In Form memiliki:
     - `email`
     - `password`
2. **Respon JSON & Redirect Otomatis:**
   - Saat submit sukses, tutup modal, tampilkan toast notifikasi sukses, lalu jalankan `window.location.href = '/dashboard'`.
3. **Logout:**
   - Mengirim request `POST /logout` dengan CSRF header, kemudian me-redirect browser ke `/`.

---

### 📌 FASE 3: Fitur Manajemen Taman (CRUD Taman)
1. **Form Tambah Taman:**
   - Modal popup dengan field:
     - **Nama Taman** (contoh: *Lahan Jagung Selatan*, *Greenhouse Melinjo*)
     - **Tipe Ekosistem** (pilihan dropdown: *corn*, *greenhouse*, *rice*, *custom*)
     - **Lokasi** (opsional, contoh: *Blok 3, Malang*)
2. **Penyimpanan Server (`TamanController@store`):**
   - Menyimpan dengan relasi `Auth::user()->tamans()->create(...)`.
   - Me-redirect langsung ke halaman monitoring detail: `route('taman.show', $taman)`.
3. **Penghapusan Taman (`TamanController@destroy`):**
   - Tombol hapus dengan konfirmasi aman.
   - Verifikasi otorisasi `abort_unless($taman->user_id === Auth::id(), 403)`.

---

### 📌 FASE 4: Logika Sensor Dinamis per Taman (`taman/show.blade.php`)
1. **Baseline Berdasarkan Jenis Lahan:**
   - **Corn (Jagung):** pH ~6.4 - 6.8, Kelembapan ~55 - 65%, Temp ~26 - 29°C, EC ~1.30 - 1.50 mS/cm.
   - **Greenhouse:** pH ~5.6 - 6.0, Kelembapan ~70 - 80%, Temp ~28 - 32°C, EC ~1.60 - 1.90 mS/cm.
   - **Rice (Padi):** pH ~5.8 - 6.2, Kelembapan ~80 - 95%, Temp ~27 - 30°C, EC ~1.10 - 1.30 mS/cm.
2. **Logika Simulasi Penurunan & Penyiraman (Sesuai Permintaan User):**
   - **Penurunan Alami:** Setiap interval waktu (misal tiap 10 detik tanpa aksi), tingkat kelembapan (`moisture`) dan nutrisi (`EC`) perlahan berkurang secara bertahap untuk mensimulasikan penguapan dan penyerapan tanah.
   - **Aksi Siram Air (Water Pump):** Nilai `moisture` meningkat (+15% hingga batas wajar) dan nilai suhu turun sedikit.
   - **Aksi Pupuk (Biofertilizer):** Nilai konduktivitas elektrik (`EC`) meningkat dan pH kembali ke titik netral ideal.
   - **Health Score:** Menghitung skor kesehatan (0 - 100) secara otomatis berdasarkan deviasi nilai sensor dari batas ideal jenis tanaman.

---

## 5. Checklist Verifikasi Manual

| No | Pengujian | Ekspektasi Hasil |
|---|---|---|
| 1 | Buka `http://localhost:8000` | Tampil `welcome.blade.php`, bisa di-scroll normal, kalkulator dan preview tab berfungsi |
| 2 | Klik tombol **Mulai Pantau** | Masuk ke `/dashboard`, tampilan rapi tidak tertutup navbar, halaman bisa di-scroll |
| 3 | Daftar akun baru (Sign Up) | Berhasil dibuat di database, login otomatis, list taman **benar-benar 0 (kosong)** |
| 4 | Tambah Taman Baru | Input nama taman dan tipe -> tersimpan ke database -> redirect ke detail taman |
| 5 | Buka Detail Taman (`/taman/{id}`) | 4 kartu sensor aktif dengan angka sesuai tipe taman |
| 6 | Uji Remote Action (Siram / Pupuk) | Statistik bereaksi naik/turun sesuai logika dan tercatat di riwayat |
| 7 | Log Out lalu Log In kembali | Data taman tetap aman tersimpan untuk akun tersebut, tidak ada data bocor ke akun lain |
