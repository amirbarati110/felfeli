<?php

namespace App\Services\Cart;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * سبد خرید مبتنی بر نشست (session). طبق PRD:
 *  - قیمت و جمع کل همیشه سمت سرور از روی داده‌ی معتبر محصول محاسبه می‌شود.
 *  - در نشست فقط sku و تعداد نگه داشته می‌شود، نه قیمت.
 *  - کالای غیرفعال/ناموجود هنگام نمایش سبد کنار گذاشته و به کاربر اعلام می‌شود.
 */
class Cart
{
    protected const KEY = 'cart';

    /** @var array<string,int> sku => quantity */
    protected array $lines;

    public function __construct(protected Session $session)
    {
        $this->lines = $this->session->get(self::KEY, []);
    }

    public function add(string $sku, int $qty = 1): void
    {
        $this->lines[$sku] = max(1, ($this->lines[$sku] ?? 0) + $qty);
        $this->persist();
    }

    public function setQuantity(string $sku, int $qty): void
    {
        if ($qty <= 0) {
            unset($this->lines[$sku]);
        } else {
            $this->lines[$sku] = min($qty, 99);
        }
        $this->persist();
    }

    public function remove(string $sku): void
    {
        unset($this->lines[$sku]);
        $this->persist();
    }

    public function clear(): void
    {
        $this->lines = [];
        $this->persist();
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    /** @return array<string,int> sku => quantity (خام، بدون اعتبارسنجی) */
    public function lines(): array
    {
        return $this->lines;
    }

    public function count(): int
    {
        return array_sum($this->lines);
    }

    /**
     * وضعیت کامل سبد با قیمت‌های معتبر سمت سرور.
     *
     * @return array{
     *   items: Collection<int,array>,
     *   removed: list<string>,
     *   adjusted: list<string>,
     *   subtotal: int,
     *   count: int
     * }
     */
    public function snapshot(): array
    {
        if ($this->lines === []) {
            return ['items' => collect(), 'removed' => [], 'adjusted' => [], 'subtotal' => 0, 'count' => 0];
        }

        $products = Product::query()
            ->whereIn('sku', array_keys($this->lines))
            ->get()
            ->keyBy('sku');

        $items = collect();
        $removed = [];
        $adjusted = [];
        $dirty = false;

        foreach ($this->lines as $sku => $qty) {
            /** @var Product|null $product */
            $product = $products->get($sku);

            if (! $product || ! $product->isOrderable()) {
                $removed[] = $product?->name ?? $sku;
                unset($this->lines[$sku]);
                $dirty = true;

                continue;
            }

            if ($product->track_stock && $product->stock_qty !== null && $qty > $product->stock_qty) {
                $qty = max(1, $product->stock_qty);
                $this->lines[$sku] = $qty;
                $adjusted[] = $product->name;
                $dirty = true;
            }

            $lineTotal = $product->price * $qty;

            $items->push([
                'sku' => $product->sku,
                'product_id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->image_url,
                'unit_price' => $product->price,
                'quantity' => $qty,
                'line_total' => $lineTotal,
                'category' => $product->category?->name,
            ]);
        }

        if ($dirty) {
            $this->persist();
        }

        return [
            'items' => $items,
            'removed' => $removed,
            'adjusted' => $adjusted,
            'subtotal' => (int) $items->sum('line_total'),
            'count' => (int) $items->sum('quantity'),
        ];
    }

    protected function persist(): void
    {
        $this->session->put(self::KEY, $this->lines);
    }
}
