<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductSearch;
use Illuminate\Http\JsonResponse;
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
        $q = ProductSearch::normalized((string) $request->string('q'));
        $categorySlug = $request->string('category')->toString();

        $category = $categorySlug
            ? Category::active()->where('slug', $categorySlug)->first()
            : null;

        $productsQuery = Product::query()
            ->visible()
            ->with('category:id,name,slug')
            ->when($category, fn ($query) => $query->where('category_id', $category->id));

        if ($q !== '') {
            ProductSearch::apply($productsQuery, $q);
        }

        $products = $productsQuery
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

    public function suggestions(Request $request): JsonResponse
    {
        $q = ProductSearch::normalized((string) $request->string('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $products = ProductSearch::apply(Product::query()->visible(), $q)
            ->ordered()
            ->limit(8)
            ->get()
            ->map(fn (Product $product) => [
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->price,
                'image_url' => $product->image_url,
            ]);

        return response()->json(['data' => $products]);
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
}
