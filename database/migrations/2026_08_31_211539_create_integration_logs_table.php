<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('channel')->default('baran');   // baran
            $table->string('direction')->default('out');   // out | in
            $table->string('event');                       // order.push , catalog.pull , ...
            $table->string('status');                      // success | failed
            $table->unsignedSmallInteger('http_status')->nullable();

            // payloadها با ماسک اطلاعات حساس ذخیره می‌شوند
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->text('message')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['channel', 'event']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
