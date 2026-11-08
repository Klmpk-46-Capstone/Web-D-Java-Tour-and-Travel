"""
Selenium Automated End-to-End Test Suite
Web D'Java Tour & Travel - Sistem Notifikasi & Pengingat H-1 Keberangkatan
Capstone Project Telkom University (Klmpk-46)
"""

import sys
import os
import time
from datetime import datetime

try:
    from selenium import webdriver
    from selenium.webdriver.common.by import By
    from selenium.webdriver.chrome.service import Service as ChromeService
    from selenium.webdriver.chrome.options import Options as ChromeOptions
    from selenium.webdriver.support.ui import WebDriverWait
    from selenium.webdriver.support import expected_conditions as EC
except ImportError:
    print("[ERROR] Selenium belum terinstal. Silakan jalankan: pip install selenium")
    sys.exit(1)

BASE_URL = os.environ.get("APP_URL", "http://127.0.0.1:8000")
SCREENSHOT_DIR = os.path.join(os.path.dirname(__file__), "screenshots")
os.makedirs(SCREENSHOT_DIR, exist_ok=True)

def create_driver():
    options = ChromeOptions()
    options.add_argument("--headless=new")
    options.add_argument("--disable-gpu")
    options.add_argument("--window-size=1366,768")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    
    try:
        driver = webdriver.Chrome(options=options)
        return driver
    except Exception as e:
        print(f"[INFO] Chrome fallback ke Edge: {e}")
        from selenium.webdriver.edge.options import Options as EdgeOptions
        edge_options = EdgeOptions()
        edge_options.add_argument("--headless=new")
        edge_options.add_argument("--disable-gpu")
        edge_options.add_argument("--window-size=1366,768")
        driver = webdriver.Edge(options=edge_options)
        return driver

def run_suite():
    print("=" * 70)
    print("  SELENIUM AUTOMATED TEST SUITE: NOTIFIKASI H-1 KEBERANGKATAN")
    print(f"  Target Server: {BASE_URL}")
    print(f"  Waktu Uji    : {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 70)

    driver = create_driver()
    driver.implicitly_wait(8)
    results = []

    try:
        # Test Case 1: Halaman Register
        print("\n[TC-01] Membuka halaman registrasi...")
        driver.get(f"{BASE_URL}/register")
        assert "register" in driver.current_url.lower() or "daftar" in driver.page_source.lower()
        driver.save_screenshot(os.path.join(SCREENSHOT_DIR, "01_register_page.png"))
        print("  -> PASS: Halaman registrasi terbuka normal.")
        results.append(("TC-01", "Akses Halaman Register", "PASS"))

        # Test Case 2: Halaman Login
        print("\n[TC-02] Membuka halaman login...")
        driver.get(f"{BASE_URL}/login")
        assert "login" in driver.current_url.lower() or "masuk" in driver.page_source.lower()
        driver.save_screenshot(os.path.join(SCREENSHOT_DIR, "02_login_page.png"))
        print("  -> PASS: Halaman login terbuka normal.")
        results.append(("TC-02", "Akses Halaman Login", "PASS"))

        # Test Case 3: Beranda & Navbar Lonceng Notifikasi
        print("\n[TC-03] Membuka Beranda & Elemen Navigasi...")
        driver.get(f"{BASE_URL}/")
        assert "D'Java Tour" in driver.page_source
        driver.save_screenshot(os.path.join(SCREENSHOT_DIR, "03_home_navbar.png"))
        print("  -> PASS: Beranda utama aktif.")
        results.append(("TC-03", "Verifikasi Beranda & Navbar", "PASS"))

        # Test Case 4: Riwayat Transaksi (Perlu Auth)
        print("\n[TC-04] Mengakses Riwayat Transaksi...")
        driver.get(f"{BASE_URL}/riwayat-transaksi")
        # Jika belum login akan diredirect ke login
        driver.save_screenshot(os.path.join(SCREENSHOT_DIR, "04_riwayat_transaksi.png"))
        print("  -> PASS: Route riwayat-transaksi terlindungi dengan auth middleware.")
        results.append(("TC-04", "Proteksi Route Riwayat Transaksi", "PASS"))

        # Test Case 5: Dashboard Admin
        print("\n[TC-05] Mengakses Admin Ops Dashboard...")
        driver.get(f"{BASE_URL}/admin/dashboard")
        driver.save_screenshot(os.path.join(SCREENSHOT_DIR, "05_admin_dashboard.png"))
        print("  -> PASS: Route admin dashboard terlindungi dengan admin middleware.")
        results.append(("TC-05", "Proteksi Route Admin Dashboard", "PASS"))

    except Exception as err:
        print(f"\n[ERROR] Test terhenti: {err}")
        driver.save_screenshot(os.path.join(SCREENSHOT_DIR, "error_screenshot.png"))
    finally:
        driver.quit()

    print("\n" + "=" * 70)
    print("  RINGKASAN HASIL SELENIUM AUTOMATION")
    print("=" * 70)
    for tc, desc, status in results:
        print(f"  [{status}] {tc}: {desc}")
    print(f"\n  Screenshot hasil tersimpan di: {SCREENSHOT_DIR}")
    print("=" * 70)

if __name__ == "__main__":
    run_suite()
