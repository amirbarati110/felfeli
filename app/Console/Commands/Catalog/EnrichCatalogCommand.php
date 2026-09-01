<?php

namespace App\Console\Commands\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * اعمال دسته‌بندی و تصویرِ محصولات از فایل enrichment
 * (تولیدشده از تطبیق نامِ اقلام باران با کاتالوگ گروه فلفلی).
 *
 *   php artisan catalog:enrich
 *   php artisan catalog:enrich --force-category   (دسته را حتی اگر دستی تنظیم شده بازنویسی کن)
 */
class EnrichCatalogCommand extends Command
{
    protected $signature = 'catalog:enrich
        {--path= : مسیر فایل enrichment json}
        {--force-category : بازنویسی دسته حتی اگر قبلاً تنظیم شده}
        {--images-only : فقط تصویرها}';

    protected $description = 'اعمال دسته‌بندی و تصویر محصولات از فایل enrichment';

    public function handle(): int
    {
        $path = $this->option('path') ?: database_path('fixtures/felfelinab-enrichment.json');

        if (! File::exists($path)) {
            $this->error("فایل enrichment یافت نشد: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode(File::get($path), true) ?: [];
        $categoryMap = Category::pluck('id', 'slug')->all();
        $imageDir = public_path('images/products');

        $cat = $img = $missing = 0;

        foreach ($rows as $row) {
            $product = Product::where('sku', (string) $row['sku'])->first();
            if (! $product) {
                $missing++;

                continue;
            }

            $update = [];

            if (! $this->option('images-only')) {
                $slug = $row['category'] ?? null;
                if ($slug && isset($categoryMap[$slug])) {
                    if ($this->option('force-category') || $product->source !== 'manual') {
                        $update['category_id'] = $categoryMap[$slug];
                        $cat++;
                    }
                }
            }

            $localImage = "images/products/{$row['sku']}.jpg";
            if (File::exists("{$imageDir}/{$row['sku']}.jpg")) {
                // فقط اگر عکس دستی آپلود نشده
                if (blank($product->image) || str_starts_with((string) $product->image, 'images/products/') || str_starts_with((string) $product->image, 'http')) {
                    $update['image'] = $localImage;
                    $img++;
                }
            }

            if ($update) {
                $product->forceFill($update)->saveQuietly();
            }
        }

        $this->info("دسته‌بندی: {$cat}   تصویر: {$img}   محصول یافت‌نشده: {$missing}");

        // هر محصولی که دسته ندارد → سایر
        $sayer = $categoryMap['sayer'] ?? null;
        if ($sayer) {
            $orphans = Product::whereNull('category_id')->update(['category_id' => $sayer]);
            if ($orphans) {
                $this->line("«{$orphans}» محصول بدون دسته به «سایر محصولات» منتقل شد.");
            }
        }

        return self::SUCCESS;
    }
}
