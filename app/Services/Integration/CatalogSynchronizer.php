<?php

namespace App\Services\Integration;

use App\Models\Category;
use App\Models\IntegrationLog;
use App\Models\Product;
use App\Services\Integration\Contracts\CatalogSource;
use App\Services\Integration\DTO\CatalogItem;
use App\Support\CategoryClassifier;
use App\Support\LogMasker;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * همگام‌سازی کاتالوگ: اقلام منبع را بر مبنای SKU در جدول products
 * upsert می‌کند. دسته‌بندی سایت را تغییر نمی‌دهد (طبق PRD دسته‌ها از پنل
 * کنترل می‌شوند)، فقط قیمت/موجودی/نام/عکس را به‌روز می‌کند.
 *
 * فیلدهای «مدیریت‌شده در پنل» (category_id، is_active، sort_order، توضیحات
 * دستی) در به‌روزرسانی دست‌نخورده می‌مانند.
 */
class CatalogSynchronizer
{
    /** @var array{created:int,updated:int,skipped:int,failed:int,rows:list<array>} */
    protected array $report;

    /** @var array<string,int> نگاشت slug دسته به id (کش داخل اجرا) */
    protected array $categoryMap = [];

    public function __construct(protected CatalogSource $source) {}

    public function sync(bool $deactivateMissing = true): array
    {
        $this->report = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'rows' => []];
        $this->categoryMap = Category::pluck('id', 'slug')->all();

        $seenSkus = [];
        $now = Carbon::now();

        foreach ($this->source->items() as $item) {
            try {
                $result = $this->upsert($item, $now);
                $seenSkus[] = $item->sku;
                $this->report[$result]++;
                $this->report['rows'][] = ['sku' => $item->sku, 'name' => $item->name, 'result' => $result];
            } catch (Throwable $e) {
                $this->report['failed']++;
                $this->report['rows'][] = ['sku' => $item->sku, 'name' => $item->name, 'result' => 'failed', 'error' => $e->getMessage()];
            }
        }

        // کالاهایی که دیگر در منبع نیستند: غیرفعال (نه حذف — ممکن است در سفارش‌های قبلی باشند)
        $deactivated = 0;
        if ($deactivateMissing && $seenSkus !== []) {
            $deactivated = Product::query()
                ->where('source', 'baran')
                ->whereNotIn('sku', $seenSkus)
                ->where('in_stock', true)
                ->update(['in_stock' => false, 'source_updated_at' => $now]);
        }

        $summary = [
            'source' => $this->source->name(),
            'created' => $this->report['created'],
            'updated' => $this->report['updated'],
            'skipped' => $this->report['skipped'],
            'failed' => $this->report['failed'],
            'deactivated_missing' => $deactivated,
            'total' => count($this->report['rows']),
            'synced_at' => $now->toIso8601String(),
        ];

        IntegrationLog::create([
            'channel' => 'baran',
            'direction' => 'in',
            'event' => 'catalog.sync',
            'status' => $this->report['failed'] > 0 ? 'failed' : 'success',
            'request' => ['source' => $this->source->name()],
            'response' => LogMasker::mask($summary),
            'message' => "ساخته:{$summary['created']} به‌روز:{$summary['updated']} رد:{$summary['skipped']} خطا:{$summary['failed']}",
        ]);

        return $summary + ['rows' => $this->report['rows']];
    }

    /** @return 'created'|'updated'|'skipped' */
    protected function upsert(CatalogItem $item, Carbon $now): string
    {
        if ($item->price <= 0) {
            return 'skipped';
        }

        /** @var Product|null $product */
        $product = Product::query()->where('sku', $item->sku)->first();

        $payload = [
            'name' => $item->name,
            'price' => $item->price,
            'compare_price' => $item->comparePrice,
            'in_stock' => $item->inStock,
            'stock_qty' => $item->stockQty,
            'track_stock' => true,
            'source' => 'baran',
            'source_updated_at' => $now,
        ];

        if (filled($item->imageUrl)) {
            $payload['image'] = $item->imageUrl;
        }

        if (! $product) {
            $slug = CategoryClassifier::classify($item->name);

            Product::create($payload + [
                'sku' => $item->sku,
                'is_active' => true,
                'description' => $item->description,
                'category_id' => $this->categoryMap[$slug] ?? $this->categoryMap[CategoryClassifier::FALLBACK] ?? null,
            ]);

            return 'created';
        }

        $product->fill($payload)->save();

        return 'updated';
    }
}
