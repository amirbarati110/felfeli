<?php

namespace App\Services\Import;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImport;
use App\Support\CategoryClassifier;
use Illuminate\Support\Facades\Storage;

/**
 * درون‌ریزی محصولات از فایل CSV (UTF-8).
 *
 * ستون‌های شناخته‌شده (سرستون فارسی یا انگلیسی):
 *   sku            | کد کالا            (الزامی، یکتا — مبنای upsert)
 *   name           | نام               (الزامی)
 *   price          | قیمت              (الزامی، ریال، عدد صحیح)
 *   category       | دسته              (نام دسته؛ نبود ⇐ حدس خودکار)
 *   stock          | موجودی            (عدد؛ >0 ⇒ موجود)
 *   in_stock       | وضعیت             (بله/خیر ، 1/0 ، true/false)
 *   image          | تصویر             (URL)
 *   sort_order     | ترتیب
 *   active         | فعال              (بله/خیر)
 *
 * توجه: طبق PRD ستون وزن/Variant لازم نیست و اگر باشد نادیده گرفته می‌شود.
 */
class ProductImporter
{
    private const HEADER_MAP = [
        'sku' => ['sku', 'کد', 'کد کالا', 'کدکالا', 'بارکد', 'شناسه'],
        'name' => ['name', 'title', 'نام', 'نام کالا', 'عنوان', 'شرح کالا'],
        'price' => ['price', 'قیمت', 'قیمت فروش', 'مبلغ', 'فی'],
        'category' => ['category', 'دسته', 'دسته بندی', 'دسته‌بندی', 'گروه', 'گروه کالا'],
        'stock' => ['stock', 'quantity', 'qty', 'موجودی', 'تعداد', 'موجودی انبار'],
        'in_stock' => ['in_stock', 'status', 'وضعیت', 'موجود'],
        'image' => ['image', 'img', 'picture', 'تصویر', 'عکس', 'لینک تصویر'],
        'sort_order' => ['sort_order', 'sort', 'order', 'ترتیب', 'اولویت'],
        'active' => ['active', 'is_active', 'enabled', 'فعال', 'نمایش'],
    ];

    public function run(ProductImport $import): ProductImport
    {
        $import->update(['status' => 'processing']);

        $report = [];
        $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $categories = Category::pluck('id', 'name')->all();
        $categoryBySlug = Category::pluck('id', 'slug')->all();

        $rows = $this->readCsv(Storage::disk($import->disk)->path($import->path));
        $header = array_shift($rows);
        $cols = $this->mapHeader($header ?? []);

        if (! isset($cols['sku'], $cols['name'], $cols['price'])) {
            $import->update([
                'status' => 'failed',
                'report' => [['row' => 1, 'result' => 'failed', 'reason' => 'ستون‌های الزامی (کد کالا، نام، قیمت) در فایل پیدا نشد.']],
            ]);

            return $import->refresh();
        }

        foreach ($rows as $i => $raw) {
            $line = $i + 2;
            $get = fn ($key) => isset($cols[$key]) ? trim((string) ($raw[$cols[$key]] ?? '')) : null;

            $sku = $this->digits($get('sku'));
            $name = $get('name');
            $price = (int) preg_replace('/\D/', '', $this->digits((string) $get('price')));

            if ($sku === '' || $name === '') {
                $counts['skipped']++;
                $report[] = ['row' => $line, 'result' => 'skipped', 'reason' => 'کد کالا یا نام خالی است'];

                continue;
            }
            if ($price <= 0) {
                $counts['skipped']++;
                $report[] = ['row' => $line, 'result' => 'skipped', 'reason' => 'قیمت نامعتبر', 'sku' => $sku];

                continue;
            }

            try {
                $categoryId = $this->resolveCategory($get('category'), $name, $categories, $categoryBySlug);

                $stock = $get('stock');
                $inStock = $get('in_stock');
                $payload = [
                    'name' => $name,
                    'price' => $price,
                    'category_id' => $categoryId,
                    'image' => filled($get('image')) ? $get('image') : null,
                    'sort_order' => is_numeric($get('sort_order')) ? (int) $get('sort_order') : 0,
                ];

                if (is_numeric($this->digits((string) $stock))) {
                    $payload['track_stock'] = true;
                    $payload['stock_qty'] = (int) $this->digits((string) $stock);
                    $payload['in_stock'] = $payload['stock_qty'] > 0;
                } elseif ($inStock !== null && $inStock !== '') {
                    $payload['in_stock'] = $this->boolish($inStock);
                }

                if (($active = $get('active')) !== null && $active !== '') {
                    $payload['is_active'] = $this->boolish($active);
                }

                $existing = Product::where('sku', $sku)->first();

                if ($existing) {
                    $existing->fill($payload + ['source' => 'excel', 'source_updated_at' => now()])->save();
                    $counts['updated']++;
                    $report[] = ['row' => $line, 'result' => 'updated', 'sku' => $sku];
                } else {
                    Product::create($payload + [
                        'sku' => $sku,
                        'is_active' => $payload['is_active'] ?? true,
                        'source' => 'excel',
                        'source_updated_at' => now(),
                    ]);
                    $counts['created']++;
                    $report[] = ['row' => $line, 'result' => 'created', 'sku' => $sku];
                }
            } catch (\Throwable $e) {
                $counts['failed']++;
                $report[] = ['row' => $line, 'result' => 'failed', 'reason' => $e->getMessage(), 'sku' => $sku];
            }
        }

        $import->update([
            'status' => $counts['failed'] > 0 ? 'completed' : 'completed',
            'total_rows' => count($rows),
            'created_count' => $counts['created'],
            'updated_count' => $counts['updated'],
            'skipped_count' => $counts['skipped'],
            'failed_count' => $counts['failed'],
            'report' => $report,
        ]);

        return $import->refresh();
    }

    private function resolveCategory(?string $name, string $productName, array $byName, array $bySlug): ?int
    {
        if (filled($name)) {
            foreach ($byName as $catName => $id) {
                if (mb_strtolower(trim($catName)) === mb_strtolower(trim($name))) {
                    return $id;
                }
            }
        }

        return $bySlug[CategoryClassifier::classify($productName)] ?? $bySlug[CategoryClassifier::FALLBACK] ?? null;
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        if (($h = fopen($path, 'r')) !== false) {
            // حذف BOM
            $bom = fread($h, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($h);
            }
            while (($data = fgetcsv($h, 0, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($h);
        }

        return $rows;
    }

    private function mapHeader(array $header): array
    {
        $cols = [];
        foreach ($header as $index => $label) {
            $label = mb_strtolower(trim(str_replace(['ي', 'ك', "\u{200c}"], ['ی', 'ک', ' '], (string) $label)));
            foreach (self::HEADER_MAP as $key => $aliases) {
                foreach ($aliases as $alias) {
                    if ($label === mb_strtolower(str_replace(['ي', 'ك'], ['ی', 'ک'], $alias))) {
                        $cols[$key] = $index;
                    }
                }
            }
        }

        return $cols;
    }

    private function digits(?string $v): string
    {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', ',', '٬'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '', ''],
            (string) $v,
        );
    }

    private function boolish(string $v): bool
    {
        return in_array(mb_strtolower(trim($v)), ['1', 'true', 'yes', 'بله', 'فعال', 'موجود', 'دارد', 'y'], true);
    }
}
