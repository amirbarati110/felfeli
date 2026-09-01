<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'slug', 'category_id', 'description',
        'price', 'compare_price',
        'track_stock', 'stock_qty', 'in_stock',
        'image', 'is_active', 'sort_order',
        'source', 'source_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_price' => 'integer',
            'track_stock' => 'boolean',
            'stock_qty' => 'integer',
            'in_stock' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'source_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name, '-', 'fa') ?: 'product-'.Str::random(6);
            }

            // موجودی صفر ⇐ ناموجود
            if ($product->track_stock) {
                $product->in_stock = (int) $product->stock_qty > 0;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('in_stock')->orderBy('sort_order')->orderBy('name');
    }

    /** آیا این محصول همین حالا قابل سفارش است؟ */
    public function isOrderable(): bool
    {
        return $this->is_active && $this->in_stock && $this->price > 0;
    }

    public function getImageUrlAttribute(): string
    {
        if (blank($this->image)) {
            return asset('images/product-placeholder.svg');
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        // فایل‌های زیر public/ (مثل images/products/123.jpg) و مسیرهای مطلق
        if (Str::startsWith($this->image, ['/', 'images/'])) {
            return asset(ltrim($this->image, '/'));
        }

        // آپلودهای پنل روی دیسک public (products/xxxx.jpg)
        return Storage::disk('public')->url($this->image);
    }

    public function hasImage(): bool
    {
        return filled($this->image);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
