<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('price_with_driver')->nullable()->after('price_per_day');
            $table->unsignedBigInteger('price_without_driver')->nullable()->after('price_with_driver');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['price_with_driver', 'price_without_driver']);
        });
    }
};
