<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->string('customer_name');
            $table->string('customer_mobile', 20);

            // delivery = ارسال در ساوه ، pickup = دریافت حضوری
            $table->string('delivery_method')->default('delivery');
            $table->text('address')->nullable();
            $table->text('note')->nullable();

            // مبالغ به ریال — همه سمت سرور بازمحاسبه می‌شوند
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('total')->default(0);

            // چرخه‌ی وضعیت سفارش (مستقل از حسابداری)
            $table->string('status')->default('new');

            // وضعیت همگام‌سازی با نرم‌افزار حسابداری باران
            $table->string('integration_status')->default('pending'); // pending | synced | failed
            $table->unsignedInteger('integration_attempts')->default(0);
            $table->text('integration_last_error')->nullable();
            $table->timestamp('integration_synced_at')->nullable();
            $table->string('integration_reference')->nullable(); // شماره فاکتور بازگشتی از باران

            // کلید یکتا برای جلوگیری از ثبت تکراری در Retry
            $table->uuid('idempotency_key')->unique();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('integration_status');
            $table->index('customer_mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
