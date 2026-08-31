<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Services\Cart\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $cart = app(Cart::class);

        return [
            ...parent::share($request),

            'app' => [
                'name' => config('app.name'),
            ],

            'auth' => [
                'user' => $request->user()
                    ? $request->user()->only('id', 'name', 'email')
                    : null,
            ],

            'store' => fn () => Setting::get('store', []),

            'cart' => fn () => [
                'count' => $cart->count(),
                'subtotal' => (int) $cart->snapshot()['subtotal'],
                'lines' => (object) $cart->lines(),
            ],

            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],

            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
