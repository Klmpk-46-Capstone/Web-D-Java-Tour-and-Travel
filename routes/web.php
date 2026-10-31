<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminManifestController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\AdminRefundController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminTripController;
use App\Http\Controllers\Admin\AdminVehicleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DesignSystemController;
use App\Http\Controllers\Home\ArmadaController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\PaketWisataController;
use App\Http\Controllers\Home\RatingController;
use App\Http\Controllers\Home\RefundController;
use App\Http\Controllers\Home\RiwayatTransaksiController;
use App\Http\Controllers\Pembayaran\PembayaranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Frontend Routes
|--------------------------------------------------------------------------
*/
$customerRoutes = function () {
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

        // Halaman Invoice Booking
        Route::get('/{bookingCode}/invoice', [RiwayatTransaksiController::class, 'invoice'])
            ->where('bookingCode', '[A-Za-z0-9_-]+')
            ->name('invoice');

        // Halaman pengajuan refund
        Route::get('/{bookingCode}/refund', [RefundController::class, 'index'])
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

        // Menyimpan pengajuan refund
        Route::post('/{bookingCode}/refund', [RefundController::class, 'store'])
            ->where('bookingCode', '[A-Za-z0-9_-]+')
            ->middleware('throttle:5,10')
            ->name('refund.submit');
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
};

/*
|--------------------------------------------------------------------------
| Booking & Pembayaran Xendit (Protected by Auth)
|--------------------------------------------------------------------------
*/
$bookingPaymentRoutes = function () {
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
};

Route::middleware(['auth', 'setlocale:id'])->group($bookingPaymentRoutes);

Route::prefix('en')
    ->name('en.')
    ->middleware(['setlocale:en', 'auth'])
    ->group($bookingPaymentRoutes);

Route::middleware('setlocale:id')->group($customerRoutes);

Route::prefix('en')
    ->name('en.')
    ->middleware('setlocale:en')
    ->group($customerRoutes);

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
| Admin Refund Decision Routes (Protected by Auth & Controller-level Role Check)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')
    ->prefix('admin/refunds')
    ->name('admin.refunds.')
    ->group(function () {
        Route::patch('/{refundId}/review', [
            AdminRefundController::class,
            'review',
        ])->whereNumber('refundId')
            ->name('review');

        Route::patch('/{refundId}/transferred', [
            AdminRefundController::class,
            'markTransferred',
        ])->whereNumber('refundId')
            ->name('transferred');
    });

/*
|--------------------------------------------------------------------------
| CMS OPS CONSOLE (Protected by Admin Auth & Role Authorization)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin', 'setlocale:id'])->prefix('admin')->name('admin.')->group(function () {
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

    // 3. Manajemen Armada
    Route::get('/armada', [AdminVehicleController::class, 'index'])->name('armada.index');
    Route::get('/armada/create', [AdminVehicleController::class, 'create'])->name('armada.create');
    Route::post('/armada', [AdminVehicleController::class, 'store'])->name('armada.store');
    Route::get('/armada/{vehicle}/edit', [AdminVehicleController::class, 'edit'])->name('armada.edit');
    Route::put('/armada/{vehicle}', [AdminVehicleController::class, 'update'])->name('armada.update');
    Route::delete('/armada/{vehicle}', [AdminVehicleController::class, 'destroy'])->name('armada.destroy');
    Route::post('/armada/{vehicle}/toggle-status', [AdminVehicleController::class, 'toggleStatus'])->name('armada.toggle-status');

    // 4. Rekomendasi Utama (Featured)
    Route::get('/featured-packages', [AdminPackageController::class, 'featured'])->name('packages.featured');

    // 5. Jadwal & Detail Trip
    Route::get('/trips', [AdminTripController::class, 'index'])->name('trips.index');
    Route::post('/trips', [AdminTripController::class, 'store'])->name('trips.store');
    Route::post('/trips/sync', [AdminTripController::class, 'sync'])->name('trips.sync');
    Route::post('/trips/quick-add-batch', [AdminTripController::class, 'quickAddBatch'])->name('trips.quick-add-batch');
    Route::put('/trips/{trip}', [AdminTripController::class, 'update'])->name('trips.update');
    Route::delete('/trips/{trip}', [AdminTripController::class, 'destroy'])->name('trips.destroy');

    // 6. Manifes Penumpang
    Route::get('/manifest', [AdminManifestController::class, 'index'])->name('manifest.index');
    Route::get('/manifest/export', [AdminManifestController::class, 'export'])->name('manifest.export');
    Route::post('/manifest/passengers', [AdminManifestController::class, 'storePassenger'])->name('manifest.passengers.store');
    Route::post('/manifest/{booking}/check-in', [AdminManifestController::class, 'checkIn'])->name('manifest.check-in');

    // 7. Akun Customer
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');

    // 8. Laporan Transaksi & Keuangan
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/transactions', [AdminReportController::class, 'index'])->name('transactions.index');
    Route::get('/reports/export-csv', [AdminReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::post('/reports/reconcile', [AdminReportController::class, 'reconcile'])->name('reports.reconcile');
    Route::get('/reports/detail/{transaction}', [AdminReportController::class, 'show'])->name('reports.show');

    // 9. Refund & Pembatalan
    Route::get('/refunds', [AdminRefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds/{refund}/approve', [AdminRefundController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{refund}/reject', [AdminRefundController::class, 'reject'])->name('refunds.reject');

    // 10. Moderasi Ulasan
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/toggle-visibility', [AdminReviewController::class, 'toggleVisibility'])->name('reviews.toggle-visibility');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});
