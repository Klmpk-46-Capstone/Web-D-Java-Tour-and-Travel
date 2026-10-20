<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('category', 100)->index();
            $table->string('route_type', 50)->default('bahari')->index(); // bahari, gunung, heritage
            $table->string('duration', 80)->default('3D2N');
            $table->unsignedBigInteger('price')->default(0);
            $table->string('image_url')->nullable();
            $table->string('badge', 80)->nullable();
            $table->decimal('rating', 3, 2)->default(4.90);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number')->default(1);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->text('activities')->nullable();
            $table->timestamps();
        });

        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('batch_name', 150);
            $table->date('departure_date')->index();
            $table->date('return_date')->nullable();
            $table->unsignedSmallInteger('quota')->default(20);
            $table->unsignedSmallInteger('booked_seats')->default(0);
            $table->string('status', 50)->default('available'); // available, almost_full, full, completed, cancelled
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_email', 150);
            $table->string('customer_phone', 50);
            $table->unsignedSmallInteger('pax')->default(1);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->string('payment_method', 100)->default('BCA Virtual Account');
            $table->string('payment_status', 50)->default('paid'); // pending, paid, refunded, cancelled
            $table->string('booking_status', 50)->default('confirmed'); // confirmed, waiting_payment, completed, refund_requested, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('transaction_code', 80)->unique();
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('payment_method', 100);
            $table->string('payment_channel', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('status', 50)->default('success'); // pending, success, failed, refunded
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('refund_code', 50)->unique();
            $table->text('reason');
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('status', 50)->default('pending'); // pending, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 150);
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('comment');
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('itineraries');
        Schema::dropIfExists('packages');
    }
};
