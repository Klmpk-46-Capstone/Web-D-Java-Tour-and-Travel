<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('package_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        // Tidak dikembalikan otomatis menjadi NOT NULL,
        // karena booking Armada dapat memiliki package_id = NULL.
    }
};
