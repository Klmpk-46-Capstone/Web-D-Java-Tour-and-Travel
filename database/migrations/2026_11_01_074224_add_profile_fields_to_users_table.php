<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')
                    ->nullable()
                    ->after('whatsapp');
            }

            if (!Schema::hasColumn('users', 'identity_number')) {
                $table->text('identity_number')
                    ->nullable()
                    ->after('profile_photo_path');
            }

            if (!Schema::hasColumn('users', 'whatsapp_notifications_enabled')) {
                $table->boolean('whatsapp_notifications_enabled')
                    ->default(false)
                    ->after('identity_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'profile_photo_path',
                'identity_number',
                'whatsapp_notifications_enabled',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
