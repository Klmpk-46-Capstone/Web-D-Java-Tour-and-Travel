<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('category', 100)->nullable();
            $table->string('duration', 80)->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('seats')->default(1);
            $table->unsignedSmallInteger('luggage_capacity')->default(0);
            $table->string('transmission', 50)->nullable();
            $table->unsignedBigInteger('price_per_day')->default(0);
            $table->boolean('includes_driver')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('destinations');
    }
};
