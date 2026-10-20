# ✦ D'Java Tour & Travel — Customer Portal & CMS OPS Console

Aplikasi web reservasi paket wisata Jawa Timur dan **Back-Office Ops Console (CMS Admin)** yang terintegrasi penuh secara *real-time* dan *data-driven*, dibangun menggunakan **Laravel 12/13**, **Tailwind CSS v4**, dan **Vite**.

---

## 📋 Daftar Isi
- [Fitur Utama Sistem](#-fitur-utama-sistem)
- [Prasyarat Sistem (Prerequisites)](#-prasyarat-sistem-prerequisites)
- [Panduan Instalasi & Menjalankan Server](#-panduan-instalasi--menjalankan-server-bebas-error)
- [Kredensial Akun Bawaan (Default Seed)](#-kredensial-akun-bawaan-default-seed)
- [Hidden Admin Gateway (Akses Rahasia Console)](#-hidden-admin-gateway-akses-rahasia-console)
- [Logika 5 Titik Integrasi (CMS ⇄ Customer)](#-logika-5-titik-integrasi-cms--customer)
- [Panduan Mengatasi Masalah (Troubleshooting)](#-panduan-mengatasi-masalah-troubleshooting)

---

## 🚀 Fitur Utama Sistem

### 1. Customer Frontend
- **Katalog & Detail Paket Wisata**: Eksplorasi rute Karimunjawa, Gunung Bromo, Kawah Ijen, Dieng, dan Yogyakarta dengan rincian jadwal harian (*itinerary*).
- **Rekomendasi Utama (Featured Showcase)**: Highlight paket terpopuler yang dikontrol langsung oleh tim admin.
- **Form Booking Real-Time**: Pemilihan batch tanggal keberangkatan dengan validasi sisa kuota kursi dinamis.
- **Riwayat Reservasi & Pengajuan Refund**: Pantau status e-tiket dan formulir pembatalan tiket mandiri.
- **Ulasan & Penilaian Pelanggan**: Testimoni terkurasi dari wisatawan terverifikasi.

### 2. CMS OPS Console (Admin Panel Sesuai Mockup Visual)
- **Dashboard Analitik & Ringkasan Operasional**: Metrik Gross Revenue, Target Q1 sparkline, total booking pax, paket terlaris mingguan, tinjauan refund disengketakan, dan widget live BMKG Maritim.
- **Grafik Tren Penjualan**: Proyeksi bulanan rute Bahari, Gunung, dan Heritage dengan indikator *peak high season*.
- **Peringatan Kuota Penuh**: Indikator trip mendekati batas kapasitas dengan tombol *quick-action* `+ Buka Batch Baru`.
- **Manifes Penumpang**: Roster resmi penumpang per jadwal batch trip siap cetak (*print-ready*).
- **Audit Finansial**: Laporan transaksi masuk dan fitur ekspor data dalam format **CSV**.
- **Moderasi Ulasan & Tiket Refund**: Persetujuan pembatalan yang secara otomatis memulihkan kuota kursi ke sistem.

---

## 🛠 Prasyarat Sistem (Prerequisites)

Pastikan lingkungan lokal Anda telah terpasang:
- **PHP** `>= 8.2` atau `8.3` (Disarankan PHP 8.3)
  - Ekstensi PHP aktif: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `fileinfo`.
- **Composer** `>= 2.2`
- **Node.js** `>= 18.x` & **NPM**
- **Database Server**: **MySQL** `>= 8.0` atau **MariaDB** `>= 10.4` (bisa menggunakan Laragon, XAMPP, atau Docker).

---

## 💻 Panduan Instalasi & Menjalankan Server (Bebas Error)

Ikuti langkah-langkah berikut secara berurutan saat pertama kali meng-clone repositori ini:

### 1. Clone Repositori
```bash
git clone https://github.com/username/Web-D-Java-Tour-and-Travel.git
cd Web-D-Java-Tour-and-Travel
```

### 2. Install Dependensi PHP (Composer)
```bash
composer install
```

### 3. Install Dependensi Frontend (NPM)
```bash
npm install
```

### 4. Konfigurasi File Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
# Windows PowerShell
copy .env.example .env

# Linux / macOS / Bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan pengaturan database Anda:
```env
APP_NAME="D'Java Tour & Travel"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=capstoneweb
DB_USERNAME=root
DB_PASSWORD=
```
> [!IMPORTANT]
> Pastikan database dengan nama `capstoneweb` sudah dibuat terlebih dahulu di phpMyAdmin / MySQL client Anda (`CREATE DATABASE capstoneweb;`).

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Migrasi Database & Seeder Data Awal
Perintah ini akan membuat semua struktur tabel (`packages`, `itineraries`, `trips`, `bookings`, `transactions`, `refunds`, `reviews`) sekaligus mengisi data awal super admin dan mockup dashboard:
```bash
php artisan migrate --seed --class=CmsTourSeeder
```
*Atau jalankan migrasi dasar diikuti seeder:*
```bash
php artisan migrate
php artisan db:seed --class=CmsTourSeeder
```

### 7. Buat Symlink Storage Publik
```bash
php artisan storage:link
```

### 8. Kompilasi Aset Frontend (Tailwind CSS & JS)
Pilih salah satu sesuai kebutuhan Anda:
- **Untuk Mode Produksi (Cepat & Siap Pakai)**:
  ```bash
  npm run build
  ```
- **Untuk Mode Pengembangan Aktif (Hot Reload)**:
  ```bash
  npm run dev
  ```

### 9. Jalankan Server Lokal Laravel
Buka terminal dan jalankan server artisan:
```bash
php artisan serve
```
Aplikasi sekarang dapat diakses melalui browser di: **`http://localhost:8000`** atau **`http://127.0.0.1:8000`**.

---

## 🔑 Kredensial Akun Bawaan (Default Seed)

Setelah menjalankan `CmsTourSeeder`, akun-akun berikut sudah langsung aktif di database:

| Peran (Role) | Nama Pengguna | Alamat Email | Password | Akses Halaman |
|---|---|---|---|---|
| **Super Admin** | Bambang Setiawan | `bambang@djavatour.com` | `password123` | CMS OPS Console (`/admin`) |
| **Admin Ops** | Admin Ops Tour | `admin@djavatour.com` | `password123` | CMS OPS Console (`/admin`) |
| **Customer Demo** | Rian Adiputra | `rian.adi@gmail.com` | `password123` | Riwayat Booking (`/customer/riwayat-booking`) |

---

## 🛡 Hidden Admin Gateway (Akses Rahasia Console)

Sesuai spesifikasi keamanan, **tidak ada tombol atau link admin** pada navbar halaman publik customer. Untuk masuk ke portal CMS Back-Office:

1. Buka halaman utama aplikasi di browser (`http://localhost:8000`).
2. Tekan kombinasi tombol shortcut rahasia berikut pada keyboard:
   - **Windows / Linux**: `Ctrl` + `Shift` + `L`
   - **macOS**: `Cmd` + `Shift` + `L`
3. Sistem akan memunculkan toast autentikasi rahasia dan mengalihkan Anda ke portal:
   ```
   http://localhost:8000/admin/gate-auth
   ```
4. Masukkan email `bambang@djavatour.com` dan password `password123`.

> [!NOTE]
> Pengguna umum atau pengunjung non-admin yang mencoba mengakses URL `/admin/*` secara langsung tanpa otorisasi akan otomatis diblokir dan dialihkan kembali.

---

## 🔄 Logika 5 Titik Integrasi (CMS ⇄ Customer)

1. **Paket Wisata & Itinerary**: Penambahan atau perubahan paket di CMS langsung mengupdate katalog di `/paket-wisata` serta rincian timeline hari-ke-hari pada `/paket-wisata/{slug}`.
2. **Rekomendasi Utama (Featured Toggle)**: Menghidupkan atau mematikan tombol `Featured` di admin langsung menentukan kemunculan kartu paket pada banner atas beranda depan customer.
3. **Jadwal & Kuota Kursi**: Tanggal batch trip diatur di CMS. Jika kuota = 0, tanggal keberangkatan otomatis terkunci (`disabled`) bertuliskan `[HABIS / FULL]`.
4. **Validasi Pembatalan & Refund**: Ketika admin menyetujui (*approve*) klaim refund, status pemesanan customer berubah menjadi `refunded/dibatalkan` dan sisa kursi pada trip terkait otomatis dipulihkan (*auto seat recovery*).
5. **Moderasi Ulasan**: Customer dapat mengirimkan review langsung dari halaman detail paket. Tombol sembunyikan/hapus di CMS secara instan mencabut ulasan dari tayangan publik dan mengkalkulasi ulang rata-rata rating paket.

---

## 🛠 Panduan Mengatasi Masalah (Troubleshooting)

### 1. `Base table or view not found: 'packages'`
- **Penyebab**: Migrasi database belum dijalankan.
- **Solusi**: Jalankan perintah `php artisan migrate --seed --class=CmsTourSeeder`.

### 2. `SQLSTATE[HY000] [1524] Plugin 'mysql_native_password' is not loaded`
- **Penyebab**: Konfigurasi autentikasi MySQL 8.x pada server lokal (seperti Laragon/XAMPP).
- **Solusi**: Buka MySQL console Anda dan jalankan perintah:
  ```sql
  ALTER USER 'root'@'localhost' IDENTIFIED WITH caching_sha2_password BY '';
  FLUSH PRIVILEGES;
  ```
  Atau gunakan koneksi SQLite dengan mengubah `DB_CONNECTION=sqlite` di file `.env`.

### 3. Tampilan Berantakan / CSS Tidak Termuat
- **Penyebab**: Aset Tailwind belum di-build.
- **Solusi**: Jalankan `npm run build` di terminal proyek.

### 4. Sesi Login Mengalami Infinite Loop / Error CSRF
- **Penyebab**: Cache konfigurasi atau sesi usang.
- **Solusi**:
  ```bash
  php artisan optimize:clear
  php artisan config:clear
  ```

---

<p align="center">
  <strong>D'Java Tour &amp; Travel System</strong> • Engineered with precision for seamless tour management.
</p>
