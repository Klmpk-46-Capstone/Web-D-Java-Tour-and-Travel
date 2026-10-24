<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles');

            $table->date('rental_date')->nullable();
            $table->unsignedSmallInteger('duration')->nullable();
            $table->string('rental_option', 50)->nullable();
            $table->unsignedBigInteger('rate')->nullable();
            $table->unsignedBigInteger('down_payment')->nullable();
            $table->string('delivery_method', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn([
                'rental_date',
                'duration',
                'rental_option',
                'rate',
                'down_payment',
                'delivery_method',
            ]);
        });
    }
};
