<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Models\Setting;
use App\Services\Cart\Cart;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(protected Cart $cart) {}

    public function show(): Response|RedirectResponse
    {
        $snapshot = $this->cart->snapshot();

        if ($snapshot['items']->isEmpty()) {
            return redirect()->route('menu.index');
        }

        return Inertia::render('Checkout/Index', [
            'items' => $snapshot['items'],
            'subtotal' => $snapshot['subtotal'],
            'checkout' => $this->checkoutSettings(),
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders): RedirectResponse
    {
        $key = 'checkout:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 8)) {
            throw ValidationException::withMessages([
                'cart' => 'تعداد تلاش‌ها زیاد بود. کمی بعد دوباره امتحان کنید.',
            ]);
        }
        RateLimiter::hit($key, decaySeconds: 300);

        $order = $orders->place($request->validated());

        // اجازه‌ی مشاهده‌ی صفحه‌ی موفقیت فقط برای همین نشست
        $request->session()->put('recent_orders', array_slice(
            array_merge($request->session()->get('recent_orders', []), [$order->order_number]),
            -5,
        ));

        return redirect()->route('order.show', $order->order_number);
    }

    protected function checkoutSettings(): array
    {
        $c = Setting::get('checkout', []);

        return [
            'delivery_fee' => (int) ($c['delivery_fee'] ?? 0),
            'min_order_total' => (int) ($c['min_order_total'] ?? 0),
            'notice' => $c['notice'] ?? 'پرداخت آنلاین نیاز نیست؛ پس از ثبت سفارش، فروشگاه برای هماهنگی با شما تماس می‌گیرد.',
            'delivery_enabled' => (bool) ($c['delivery_enabled'] ?? true),
            'pickup_enabled' => (bool) ($c['pickup_enabled'] ?? true),
        ];
    }
}
