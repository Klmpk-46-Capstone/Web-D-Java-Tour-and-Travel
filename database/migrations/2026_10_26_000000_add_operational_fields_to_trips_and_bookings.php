<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detail operasional trip: armada, driver, titik kumpul, dan jam berangkat.
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('driver_name', 120)->nullable();
            $table->string('meeting_point', 200)->nullable();
            $table->string('departure_time', 5)->nullable(); // format HH:MM
        });

        // Check-in penumpang pada manifes (per pemesan / rombongan).
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by');
            $table->dropColumn('checked_in_at');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn(['driver_name', 'meeting_point', 'departure_time']);
        });
    }
};
