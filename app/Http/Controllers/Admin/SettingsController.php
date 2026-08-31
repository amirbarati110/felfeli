<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/Edit', [
            'store' => Setting::get('store', []),
            'checkout' => Setting::get('checkout', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store.name' => ['required', 'string', 'max:120'],
            'store.phone' => ['nullable', 'string', 'max:20'],
            'store.about' => ['nullable', 'string', 'max:500'],
            'store.work_time' => ['nullable', 'string', 'max:500'],
            'store.instagram' => ['nullable', 'string', 'max:255'],
            'store.location_link' => ['nullable', 'string', 'max:500'],

            'checkout.delivery_fee' => ['required', 'integer', 'min:0'],
            'checkout.min_order_total' => ['required', 'integer', 'min:0'],
            'checkout.notice' => ['required', 'string', 'max:500'],
            'checkout.delivery_enabled' => ['boolean'],
            'checkout.pickup_enabled' => ['boolean'],
        ]);

        Setting::put('store', $data['store']);
        Setting::put('checkout', $data['checkout']);

        return back()->with('success', 'تنظیمات ذخیره شد.');
    }
}
