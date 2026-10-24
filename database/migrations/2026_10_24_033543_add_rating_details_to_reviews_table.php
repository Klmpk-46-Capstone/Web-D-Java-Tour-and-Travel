
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->json('service_ratings')->nullable();
            $table->json('experience_tags')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('identity_verified')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn([
                'service_ratings',
                'experience_tags',
                'photos',
                'is_public',
                'identity_verified',
            ]);
        });
    }
};
