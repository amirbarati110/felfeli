<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * دسته‌بندی‌های فروشگاه — بر پایه‌ی ساختار واقعیِ گروه فلفلی و ترکیب اقلام باران.
     * slugها با App\Support\CategoryClassifier و فایل enrichment هماهنگ‌اند.
     */
    public const CATEGORIES = [
        ['slug' => 'sabzijat', 'name' => 'سبزیجات و فرنگیجات', 'icon' => 'leafy-green'],
        ['slug' => 'sabzi-sorkh', 'name' => 'سبزیجات سرخ‌شده', 'icon' => 'flame'],
        ['slug' => 'sabzi-khoshk', 'name' => 'سبزی خشک', 'icon' => 'wheat'],
        ['slug' => 'adviye', 'name' => 'ادویه و چاشنی', 'icon' => 'shaker'],
        ['slug' => 'rob-torshi', 'name' => 'رب، ترشی و شور', 'icon' => 'jar'],
        ['slug' => 'roghan', 'name' => 'روغن‌ها و ارده', 'icon' => 'droplet'],
        ['slug' => 'hobubat', 'name' => 'حبوبات و غلات', 'icon' => 'bean'],
        ['slug' => 'khoshkbar', 'name' => 'خشکبار و میوه خشک', 'icon' => 'nut'],
        ['slug' => 'asal-moraba', 'name' => 'عسل، شیره و مربا', 'icon' => 'honey'],
        ['slug' => 'araghijat', 'name' => 'عرقیجات، گلاب و شربت', 'icon' => 'flask'],
        ['slug' => 'chai-damnush', 'name' => 'چای و دمنوش', 'icon' => 'cup-soda'],
        ['slug' => 'amade', 'name' => 'محصولات آماده و پروتئینی', 'icon' => 'chef-hat'],
        ['slug' => 'nabat', 'name' => 'نبات و شیرینی', 'icon' => 'candy'],
        ['slug' => 'sayer', 'name' => 'سایر محصولات', 'icon' => 'shopping-basket'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $i => $row) {
            Category::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'icon' => $row['icon'],
                    'sort_order' => ($i + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }
}
