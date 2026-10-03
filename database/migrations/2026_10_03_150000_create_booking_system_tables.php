<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Venues
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city')->index();
            $table->string('phone');
            $table->unsignedInteger('dp_percent')->default(30); // 10% - 100%
            $table->unsignedInteger('cancel_free_hours')->default(24);
            $table->unsignedInteger('cancel_refund_percent')->default(100);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // 2. Courts
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->string('name');
            $table->string('sport_type')->index(); // futsal, badminton, basketball, tennis, mini_soccer
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Operating Hours per Court
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 1=Monday ... 6=Saturday
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('23:00:00');
            $table->unsignedInteger('slot_duration_minutes')->default(60);
            $table->timestamps();

            $table->unique(['court_id', 'day_of_week']);
        });

        // 4. Court Prices (Weekday / Weekend / Regular / Prime)
        Schema::create('court_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->string('day_type')->default('all'); // weekday, weekend, all
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('23:00:00');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });

        // 5. Schedule Blocks (Maintenance / Event)
        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('reason');
            $table->timestamps();

            $table->index(['court_id', 'start_at', 'end_at']);
        });

        // 6. Bookings
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->decimal('total_price', 12, 2);
            $table->decimal('dp_amount', 12, 2);
            $table->string('status')->default('PENDING_PAYMENT')->index(); 
            // PENDING_PAYMENT, CONFIRMED, COMPLETED, EXPIRED, CANCELLED, NO_SHOW
            $table->dateTime('expires_at')->index();
            $table->unsignedInteger('cancel_free_hours_snapshot')->default(24);
            $table->unsignedInteger('cancel_refund_percent_snapshot')->default(100);
            $table->dateTime('cancelled_at')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->string('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Booking Slots (Crucial Concurrency & Anti Double Booking Guard)
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->dateTime('slot_start');
            $table->dateTime('slot_end');
            $table->decimal('price', 12, 2);
            $table->timestamps();

            // CRITICAL UNIQUE CONSTRAINT: Guaranteed atomic prevention of double booking
            $table->unique(['court_id', 'slot_start']);
            $table->index(['court_id', 'slot_start', 'slot_end']);
        });

        // 8. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('type')->default('DP'); // DP, lunas
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending')->index(); 
            // pending, settlement, expire, cancel, refund
            $table->string('payment_type')->nullable(); // bank_transfer, qris, gopay, etc.
            $table->string('gateway_ref')->nullable(); // transaction_id
            $table->string('snap_token')->nullable();
            $table->text('snap_redirect_url')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        // 9. Webhook Logs
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('midtrans');
            $table->string('order_id')->nullable()->index();
            $table->json('payload');
            $table->boolean('signature_valid')->default(false);
            $table->string('status')->default('received'); // received, processed, ignored, error
            $table->text('error_message')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });

        // 10. Notifications Log
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel')->default('email'); // email, system
            $table->string('type'); // booking_created, payment_settlement, booking_expired, reminder
            $table->string('title');
            $table->text('message');
            $table->string('status')->default('sent'); // sent, failed
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_slots');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('schedule_blocks');
        Schema::dropIfExists('court_prices');
        Schema::dropIfExists('operating_hours');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('venues');
    }
};
