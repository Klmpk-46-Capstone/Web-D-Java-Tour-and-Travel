<?php

use App\Http\Controllers\Pembayaran\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Route API untuk menerima notifikasi pembayaran dari Xendit.
| Endpoint ini dipanggil oleh server Xendit, bukan oleh pengguna
| yang sedang login. Validasi callback token dilakukan di controller.
|
*/

Route::post(
    '/webhooks/xendit',
    [XenditWebhookController::class, 'handle']
)->name('webhooks.xendit');
