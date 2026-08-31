<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Cart\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(protected Cart $cart) {}

    public function show(): Response
    {
        $snapshot = $this->cart->snapshot();

        return Inertia::render('Cart/Index', [
            'items' => $snapshot['items'],
            'subtotal' => $snapshot['subtotal'],
            'removed' => $snapshot['removed'],
            'adjusted' => $snapshot['adjusted'],
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'exists:products,sku'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::where('sku', $data['sku'])->first();

        if (! $product->isOrderable()) {
            return back()->with('error', 'این کالا در حال حاضر موجود نیست.');
        }

        $this->cart->add($data['sku'], $data['qty'] ?? 1);

        return back(303)->with('success', 'به سبد اضافه شد.');
    }

    public function update(Request $request, string $sku): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->setQuantity($sku, $data['qty']);

        return back(303);
    }

    public function remove(string $sku): RedirectResponse
    {
        $this->cart->remove($sku);

        return back(303)->with('success', 'از سبد حذف شد.');
    }
}
