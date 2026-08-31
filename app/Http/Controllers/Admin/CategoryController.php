<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::ordered()
                ->withCount('products')
                ->get()
                ->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'icon' => $c->icon,
                    'products_count' => $c->products_count,
                    'is_active' => $c->is_active,
                    'sort_order' => $c->sort_order,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Form', ['category' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request));

        return redirect()->route('admin.categories.index')->with('success', 'دسته ساخته شد.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => $category->only('id', 'name', 'slug', 'icon', 'is_active', 'sort_order'),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'دسته به‌روزرسانی شد.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'این دسته محصول دارد و حذف نمی‌شود. ابتدا محصولات را منتقل کنید.');
        }

        $category->delete();

        return back()->with('success', 'دسته حذف شد.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        foreach ($request->input('ids', []) as $position => $id) {
            Category::whereKey($id)->update(['sort_order' => ($position + 1) * 10]);
        }

        return back();
    }

    protected function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($category)],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
