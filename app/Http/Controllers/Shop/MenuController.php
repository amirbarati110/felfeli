<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    /**
     * صفحه‌ی اصلی = منوی محصولات. جستجو و فیلتر دسته و صفحه‌بندی همگی اینجا،
     * بدون بارگذاری کامل صفحه (Inertia partial reload سمت کلاینت).
     */
    public function index(Request $request): Response
    {
        $q = $this->normalize((string) $request->string('q'));
        $categorySlug = $request->string('category')->toString();

        $category = $categorySlug
            ? Category::active()->where('slug', $categorySlug)->first()
            : null;

        $products = Product::query()
            ->visible()
            ->with('category:id,name,slug')
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($q !== '', function ($query) use ($q) {
                foreach (preg_split('/\s+/', $q) as $term) {
                    $query->where('name', 'like', "%{$term}%");
                }
            })
            ->ordered()
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Product $p) => [
                'sku' => $p->sku,
                'name' => $p->name,
                'price' => $p->price,
                'compare_price' => $p->compare_price,
                'in_stock' => $p->in_stock,
                'image_url' => $p->image_url,
                'category' => $p->category?->name,
            ]);

        return Inertia::render('Menu/Index', [
            'categories' => fn () => $this->categories(),
            'products' => $products,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $categorySlug,
            ],
            'activeCategory' => $category?->only(['name', 'slug']),
            'noResult' => $q !== '' && $products->total() === 0,
        ]);
    }

    protected function categories(): array
    {
        return Category::active()
            ->ordered()
            ->withCount(['products as products_count' => fn ($q) => $q->visible()])
            ->get()
            ->map(fn (Category $c) => [
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'count' => $c->products_count,
            ])
            ->all();
    }

    /** یکسان‌سازی ورودی فارسی: ي→ی ، ك→ک ، حذف نیم‌فاصله‌ی اضافی */
    protected function normalize(string $value): string
    {
        $value = str_replace(['ي', 'ك', 'ﻙ', 'ﮐ', "\u{200c}"], ['ی', 'ک', 'ک', 'ک', ' '], $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }
}
