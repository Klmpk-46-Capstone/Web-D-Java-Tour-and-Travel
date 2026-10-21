<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'selected_armada')) {
                $table->string('selected_armada', 80)->nullable()->after('pax');
            }
            if (! Schema::hasColumn('bookings', 'identity_file_path')) {
                $table->string('identity_file_path')->nullable()->after('selected_armada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'identity_file_path')) {
                $table->dropColumn('identity_file_path');
            }
            if (Schema::hasColumn('bookings', 'selected_armada')) {
                $table->dropColumn('selected_armada');
            }
        });
    }
};
