<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')
                    ->default(false)
                    ->after('role');
            }

            if (!Schema::hasColumn('users', 'blacklist_reason')) {
                $table->text('blacklist_reason')
                    ->nullable()
                    ->after('is_blacklisted');
            }

            if (!Schema::hasColumn('users', 'blacklisted_at')) {
                $table->timestamp('blacklisted_at')
                    ->nullable()
                    ->after('blacklist_reason');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')
                    ->nullable()
                    ->after('blacklisted_at');
            }

            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')
                    ->nullable()
                    ->after('last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'is_blacklisted',
                'blacklist_reason',
                'blacklisted_at',
                'last_login_at',
                'last_seen_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

