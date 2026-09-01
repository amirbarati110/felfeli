<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Services\Integration\CatalogSynchronizer;
use App\Services\Integration\IntegrationManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@felfeli.test'],
            ['name' => 'مدیر فروشگاه', 'password' => Hash::make('password')],
        );

        $this->call(CategorySeeder::class);

        // تنظیمات پیش‌فرض فروشگاه (طبق پاسخ settings منوی باران)
        Setting::put('store', [
            'name' => 'فروشگاه فلفلی ساوه',
            'phone' => '09035553532',
            'about' => 'تنوعی بی‌نظیر از محصولات ارگانیک و مواد غذایی سالم',
            'work_time' => "صبح‌ها ۹ تا ۱۴\nبعدازظهرها ۱۶ تا ۲۲\nجمعه‌ها ۱۷ تا ۲۱",
            'instagram' => 'https://www.instagram.com/felfeli_saveh/',
            'location_link' => 'https://neshan.org/maps/places/976507aebd34f6913e12649b632c0fe1',
        ]);

        Setting::put('checkout', [
            'delivery_fee' => 0,            // ریال — تا اعلام هزینه‌ی ارسال ساوه
            'min_order_total' => 0,         // ریال
            'notice' => 'پرداخت آنلاین نیاز نیست؛ پس از ثبت سفارش، فروشگاه برای هماهنگی نهایی با شما تماس می‌گیرد.',
            'delivery_enabled' => true,
            'pickup_enabled' => true,
        ]);

        // در محیط توسعه، کاتالوگ را از فایل نمونه پر کن و با داده‌ی گروه فلفلی غنی کن
        if (app()->environment('local', 'testing')) {
            $source = app(IntegrationManager::class)->catalogSource('fixture');
            (new CatalogSynchronizer($source))->sync(deactivateMissing: false);

            \Illuminate\Support\Facades\Artisan::call('catalog:enrich');
        }
    }
}
