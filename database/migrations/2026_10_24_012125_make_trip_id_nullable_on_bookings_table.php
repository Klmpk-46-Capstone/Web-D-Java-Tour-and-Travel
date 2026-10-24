<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('trip_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        // Jangan mengubah kembali menjadi NOT NULL secara otomatis,
        // karena booking Armada mungkin sudah menggunakan trip_id = NULL.
    }
};
