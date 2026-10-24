<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('xendit_payment_request_id')
                ->nullable()
                ->index();

            $table->string('payment_stage')
                ->default('dp');

            $table->json('payment_instruction')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex([
                'xendit_payment_request_id',
            ]);

            $table->dropColumn([
                'xendit_payment_request_id',
                'payment_stage',
                'payment_instruction',
            ]);
        });
    }
};
