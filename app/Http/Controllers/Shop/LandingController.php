<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __invoke(): Response|RedirectResponse
    {
        $store = Setting::get('store', []);

        if (! ($store['landing_enabled'] ?? true)) {
            return redirect()->route('menu.index');
        }

        return Inertia::render('Landing', [
            'store' => [
                'name' => $store['name'] ?? config('app.name'),
                'about' => $store['about'] ?? null,
                'phone' => $store['phone'] ?? null,
                'work_time' => $store['work_time'] ?? null,
                'instagram' => $store['instagram'] ?? null,
                'location_link' => $store['location_link'] ?? null,
            ],
        ]);
    }
}
