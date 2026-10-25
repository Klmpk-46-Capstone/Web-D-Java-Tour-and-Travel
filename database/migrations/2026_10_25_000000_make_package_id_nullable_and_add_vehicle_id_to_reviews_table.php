<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ulasan armada tidak selalu memiliki package_id.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE reviews MODIFY package_id BIGINT UNSIGNED NULL'
            );
        } else {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreignId('package_id')->nullable()->change();
            });
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                ->nullable()
                ->after('package_id')
                ->constrained('vehicles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE reviews MODIFY package_id BIGINT UNSIGNED NOT NULL'
            );
        } else {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreignId('package_id')->nullable(false)->change();
            });
        }
    }
};
