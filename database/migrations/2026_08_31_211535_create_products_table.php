<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // شناسه‌ی مرجع و یکتا (کد کالای انبار باران) — مبنای مچ و upsert
            $table->string('sku')->unique();

            $table->string('name');
            $table->string('slug')->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();

            // قیمت به ریال، صحیح — منبع معتبر قیمت سمت سرور است
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('compare_price')->nullable();

            // موجودی / وضعیت فروش
            $table->boolean('track_stock')->default(false);
            $table->integer('stock_qty')->nullable();
            $table->boolean('in_stock')->default(true);

            $table->string('image')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // ردیابی منبع داده (baran / excel / manual)
            $table->string('source')->nullable();
            $table->timestamp('source_updated_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'in_stock']);
            $table->index(['category_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
