# 🤖 Panduan Penggunaan Selenium Testing Suite
## Web D'Java Tour & Travel - Sistem Notifikasi & Pengingat H-1

Folder ini berisi berkas pengujian otomatis menggunakan **Selenium**:
1. **`DJava_Tour_Notification_Testing.side`**: Berkas Project resmi **Selenium IDE (Browser Extension)**.
2. **`test_selenium_automated.py`**: Skrip otomatis **Selenium WebDriver (Python)**.
3. **`screenshots/`**: Folder penyimpanan bukti screenshot visual hasil eksekusi pengujian.

---

## 📌 Cara 1: Menggunakan Ekstensi Browser Selenium IDE (Rekomendasi untuk Laporan Capstone)

Format berkas `.side` adalah format standar dari ekstensi **Selenium IDE**.

### Langkah-langkah:
1. Pastikan server web Laravel sudah berjalan:
   ```powershell
   php artisan serve
   ```
2. Pasang ekstensi **Selenium IDE** di browser:
   * **Google Chrome**: Cari "Selenium IDE" di [Chrome Web Store](https://chromewebstore.google.com/detail/selenium-ide/mooikfagdpfahekgfdhhbgbfndbdnga)
   * **Microsoft Edge**: Pasang via Edge Add-ons / Chrome Web Store
3. Buka ekstensi **Selenium IDE** dengan mengklik ikon extension di toolbar browser.
4. Pada menu awal Selenium IDE, pilih:
   👉 **"Open an existing project"**
5. Pilih berkas:
   `testing_selenium/DJava_Tour_Notification_Testing.side`
6. Seluruh skenario pengujian akan langsung muncul di panel kiri:
   * `TC01_Registrasi_Customer_Baru`
   * `TC02_Login_Customer`
   * `TC03_Verifikasi_Navbar_Lonceng_Notifikasi`
   * `TC04_Verifikasi_Banner_H1_Riwayat_Transaksi`
   * `TC05_Verifikasi_Pusat_Notifikasi_Profil`
   * `TC06_Admin_Dashboard_Card_H1_Dan_Checklist`
   * `TC07_Admin_Header_Lonceng_Dropdown`
7. Klik tombol **"Run all tests"** (ikon play ganda) di bagian atas untuk menjalankan seluruh pengujian otomatis langsung di browser kamu!

---

## 📌 Cara 2: Menjalankan Skrip Otomatis Python Selenium (Headless)

Kamu juga dapat menjalankan pengujian otomatis berbasis Python yang akan mengontrol browser secara otomatis:

```powershell
# Jalankan skrip selenium
python testing_selenium/test_selenium_automated.py
```

Skrip akan secara otomatis:
* Membuka browser Chrome / Edge di latar belakang (*headless mode*).
* Melakukan pengetesan rute & elemen notifikasi.
* Mengambil tangkapan layar (*screenshot*) otomatis dan menyimpannya di folder `testing_selenium/screenshots/`.

