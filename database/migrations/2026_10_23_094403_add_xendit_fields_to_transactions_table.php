<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable()->unique();
            $table->text('payment_url')->nullable();
            $table->string('external_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['xendit_invoice_id']);
            $table->dropUnique(['external_id']);
            $table->dropColumn([
                'xendit_invoice_id',
                'payment_url',
                'external_id',
            ]);
        });
    }
};
