
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('bank_name', 100)->nullable()->after('reason');
            $table->text('bank_account')->nullable()->after('bank_name');
            $table->text('account_holder')->nullable()->after('bank_account');
            $table->timestamp('transferred_at')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account',
                'account_holder',
                'transferred_at',
            ]);
        });
    }
};
