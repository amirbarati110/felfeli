<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class OrderController extends Controller
{
    public function show(Request $request, string $orderNumber): Response|RedirectResponse
    {
        $allowed = $request->session()->get('recent_orders', []);

        if (! in_array($orderNumber, $allowed, true)) {
            abort(HttpResponse::HTTP_NOT_FOUND);
        }

        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();
        $store = Setting::get('store', []);

        return Inertia::render('Order/Success', [
            'order' => [
                'number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'delivery_method' => $order->delivery_method->value,
                'delivery_method_label' => $order->delivery_method->label(),
                'address' => $order->address,
                'note' => $order->note,
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'total' => $order->total,
                'status_label' => $order->status->label(),
                'items' => $order->items->map(fn ($i) => [
                    'name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'line_total' => $i->line_total,
                ]),
            ],
            'store' => [
                'name' => $store['name'] ?? config('app.name'),
                'phone' => $store['phone'] ?? null,
            ],
        ]);
    }
}
