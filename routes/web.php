<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\AdminRefundController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminTripController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DesignSystemController;
use App\Http\Controllers\Home\ArmadaController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\PaketWisataController;
use App\Http\Controllers\Home\RatingController;
use App\Http\Controllers\Home\RiwayatTransaksiController;
use App\Http\Controllers\Pembayaran\PembayaranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

// Paket Wisata & Detail
Route::get('/paket-wisata', [PaketWisataController::class, 'packages'])
    ->name('packages.index');

// Katalog Armada & Detail
Route::get('/armada', [ArmadaController::class, 'armada'])
    ->name('armada');

Route::get('/armada/{vehicle:slug}', [ArmadaController::class, 'armadaDetail'])
    ->name('armada.show');

Route::get('/armada/{vehicle:slug}/formulir', [ArmadaController::class, 'armadaBookingForm'])
    ->name('armada.booking.form');

Route::post('/armada/{vehicle:slug}/booking', [
    ArmadaController::class,
    'storeBooking',
])
    ->middleware('auth')
    ->name('armada.booking.store');

// Pratinjau internal design system (local/testing only)
Route::get('/design-system', [DesignSystemController::class, 'index'])
    ->name('design-system');

Route::get('/paket-wisata/{slug}', [PaketWisataController::class, 'packageDetail'])
    ->name('packages.show');

Route::get('/paket-wisata/{slug}/formulir', [PaketWisataController::class, 'bookingForm'])
    ->name('booking.form');

Route::post('/paket-wisata/{package}/review', [PaketWisataController::class, 'storeReview'])
    ->name('packages.review.store');

// Live Check Trip Quota
Route::get('/trips/{trip}/quota-check', [PaketWisataController::class, 'checkTripQuota'])
    ->name('trips.check-quota');

// Riwayat Transaksi & Refund (Customer Bookings Group)
Route::middleware('auth')->prefix('riwayat-transaksi')->name('customer.bookings.')->group(function () {
    Route::get('/', [RiwayatTransaksiController::class, 'customerBookings'])
        ->name('index');

    Route::get('/{bookingCode}', [RiwayatTransaksiController::class, 'detail'])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('detail');

    // Halaman pengajuan refund
    Route::get('/{bookingCode}/refund', [
        RiwayatTransaksiController::class,
        'showRefundPage',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('refund-page');

    // Halaman pembatalan booking
    Route::get('/{bookingCode}/batal', [
        RiwayatTransaksiController::class,
        'showCancelPage',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('cancel-page');

    // Halaman formulir rating
    Route::get('/{bookingCode}/rating', [
        RatingController::class,
        'create',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('rating.create');

    // Menyimpan rating
    Route::post('/{bookingCode}/rating', [
        RatingController::class,
        'store',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->middleware('throttle:5,1')
        ->name('rating');

    Route::post('/{bookingCode}/refund', [RiwayatTransaksiController::class, 'requestRefund'])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('refund');
});

/*
|--------------------------------------------------------------------------
| Booking & Pembayaran Xendit (Protected by Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/booking/store', [
        PaketWisataController::class,
        'storeBooking',
    ])->name('booking.store');

    Route::get('/pembayaran/{bookingCode}/mulai', [
        PembayaranController::class,
        'mulaiPembayaran',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('pembayaran.mulai');

    Route::post('/pembayaran/{bookingCode}/buat', [
        PembayaranController::class,
        'buatPembayaran',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('pembayaran.buat');

    Route::get('/pembayaran/{bookingCode}/instruksi', [
        PembayaranController::class,
        'instruksiPembayaran',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('pembayaran.instruksi');

    Route::post('/pembayaran/{bookingCode}/xendit', [
        PembayaranController::class,
        'buatInvoice',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('pembayaran.xendit');

    Route::post('/pembayaran/{bookingCode}/cek-status', [
        PembayaranController::class,
        'cekStatusPembayaran',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('pembayaran.cek-status');

    Route::get('/pembayaran/{bookingCode}/cek-status-otomatis', [
        PembayaranController::class,
        'cekStatusOtomatis',
    ])
        ->where('bookingCode', '[A-Za-z0-9_-]+')
        ->name('pembayaran.cek-status-otomatis');

    Route::get('/pembayaran/sukses', [
        PembayaranController::class,
        'success',
    ])->name('pembayaran.success');

    Route::get('/pembayaran/gagal', [
        PembayaranController::class,
        'failure',
    ])->name('pembayaran.failure');
});

/*
|--------------------------------------------------------------------------
| Customer Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Hidden Admin Gateway (Restricted Access)
| Triggered via Ctrl+Shift+L / Cmd+Shift+L or Direct Hidden URL
|--------------------------------------------------------------------------
*/
Route::get('/admin/gate-auth', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/gate-auth', [AdminAuthController::class, 'login'])->name('admin.login.store');

/*
|--------------------------------------------------------------------------
| CMS OPS CONSOLE (Protected by Admin Auth & Role Authorization)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard Analitik
    Route::get('/', [AdminDashboardController::class, 'index'])->name('root');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/real-time-feed', [AdminDashboardController::class, 'realTimeFeed'])->name('realtime.feed');

    // 2. Paket & Itinerary
    Route::get('/packages', [AdminPackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create', [AdminPackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [AdminPackageController::class, 'store'])->name('packages.store');
    Route::get('/packages/{package}/edit', [AdminPackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}', [AdminPackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [AdminPackageController::class, 'destroy'])->name('packages.destroy');
    Route::post('/packages/{package}/toggle-featured', [AdminPackageController::class, 'toggleFeatured'])->name('packages.toggle-featured');
    Route::post('/packages/{package}/toggle-status', [AdminPackageController::class, 'toggleStatus'])->name('packages.toggle-status');

    // 3. Rekomendasi Utama (Featured)
    Route::get('/featured-packages', [AdminPackageController::class, 'featured'])->name('packages.featured');

    // 4. Jadwal & Kuota Trip
    Route::get('/trips', [AdminTripController::class, 'index'])->name('trips.index');
    Route::post('/trips', [AdminTripController::class, 'store'])->name('trips.store');
    Route::put('/trips/{trip}', [AdminTripController::class, 'update'])->name('trips.update');
    Route::delete('/trips/{trip}', [AdminTripController::class, 'destroy'])->name('trips.destroy');
    Route::post('/trips/quick-add-batch', [AdminTripController::class, 'quickAddBatch'])->name('trips.quick-add-batch');

    // 5. Manifes Penumpang
    Route::get('/manifest', [AdminBookingController::class, 'manifest'])->name('manifest.index');

    // 6. Akun Customer
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');

    // 7. Laporan Transaksi & Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [AdminReportController::class, 'exportCsv'])->name('reports.export-csv');

    // 8. Refund & Pembatalan
    Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refund}/approve', [AdminRefundController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');

    // 9. Moderasi Ulasan
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/toggle-visibility', [AdminReviewController::class, 'toggleVisibility'])->name('reviews.toggle-visibility');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});
