<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * دسته‌بندی‌های پایه طبق PRD. slugها با CategoryClassifier هماهنگ‌اند.
     */
    public const CATEGORIES = [
        ['slug' => 'sabzijat', 'name' => 'سبزیجات', 'icon' => 'leafy-green'],
        ['slug' => 'sabzi-khoshk', 'name' => 'سبزی خشک', 'icon' => 'wheat'],
        ['slug' => 'adviye-jat', 'name' => 'ادویه‌جات', 'icon' => 'flame'],
        ['slug' => 'aab-limu-serke', 'name' => 'رب، آبلیمو، آبغوره و سرکه', 'icon' => 'citrus'],
        ['slug' => 'roghan', 'name' => 'روغن‌ها', 'icon' => 'droplet'],
        ['slug' => 'hobubat', 'name' => 'حبوبات', 'icon' => 'bean'],
        ['slug' => 'khoshkbar', 'name' => 'خشکبار', 'icon' => 'nut'],
        ['slug' => 'asal-shireh', 'name' => 'عسل و شیره', 'icon' => 'honey'],
        ['slug' => 'moraba', 'name' => 'مربا و ترشیجات خانگی', 'icon' => 'jam'],
        ['slug' => 'torshi-shoor', 'name' => 'ترشی و شور', 'icon' => 'jar'],
        ['slug' => 'noshidani-damnush', 'name' => 'نوشیدنی و دمنوش', 'icon' => 'cup-soda'],
        ['slug' => 'nabat-shirini', 'name' => 'نبات و شیرینی', 'icon' => 'candy'],
        ['slug' => 'amade-protein', 'name' => 'محصولات آماده و پروتئینی', 'icon' => 'chef-hat'],
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
