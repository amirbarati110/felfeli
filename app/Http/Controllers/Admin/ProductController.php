<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with('category:id,name')
            ->when($request->string('q')->toString(), fn ($qr, $q) => $qr->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->when($request->filled('category'), fn ($qr) => $qr->where('category_id', $request->integer('category')))
            ->when($request->string('stock')->toString() === 'in', fn ($qr) => $qr->where('in_stock', true))
            ->when($request->string('stock')->toString() === 'out', fn ($qr) => $qr->where('in_stock', false))
            ->when($request->string('status')->toString() === 'active', fn ($qr) => $qr->where('is_active', true))
            ->when($request->string('status')->toString() === 'inactive', fn ($qr) => $qr->where('is_active', false))
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Product $p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'price' => $p->price,
                'category' => $p->category?->name,
                'in_stock' => $p->in_stock,
                'stock_qty' => $p->stock_qty,
                'is_active' => $p->is_active,
                'image_url' => $p->image_url,
                'source' => $p->source,
            ]);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only('q', 'category', 'stock', 'status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Form', [
            'product' => null,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Product::create($data + ['source' => 'manual']);

        return redirect()->route('admin.products.index')->with('success', 'محصول ساخته شد.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Form', [
            'product' => [
                ...$product->only('id', 'sku', 'name', 'category_id', 'description', 'price', 'compare_price', 'track_stock', 'stock_qty', 'in_stock', 'is_active', 'sort_order', 'source'),
                'image_url' => $product->image_url,
            ],
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        if ($product->source === 'baran') {
            $data = $request->validate(['image' => ['required', 'image', 'max:4096']]);
            $product->update(['image' => $data['image']->store('products', 'public')]);

            return redirect()->route('admin.products.index')->with('success', 'تصویر کالا به‌روزرسانی شد.');
        }

        $product->update($this->validated($request, $product));

        return redirect()->route('admin.products.index')->with('success', 'محصول به‌روزرسانی شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // حذف نرم: غیرفعال‌سازی (ممکن است در سفارش‌های قبلی باشد)
        $product->update(['is_active' => false, 'in_stock' => false]);

        return back()->with('success', 'محصول غیرفعال شد.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        foreach ($request->input('ids', []) as $position => $id) {
            Product::whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }

    protected function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0'],
            'compare_price' => ['nullable', 'integer', 'min:0'],
            'track_stock' => ['boolean'],
            'stock_qty' => ['nullable', 'integer'],
            'in_stock' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($data['image']);
        }

        return $data;
    }
}
