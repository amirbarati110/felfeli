<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncBaranCatalogJob;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Services\Integration\FelfeliPhotoSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class IntegrationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Integration/Index', [
            'config' => [
                'catalog_driver' => config('integration.catalog_driver'),
                'order_driver' => config('integration.order_driver'),
                'pack_number' => config('integration.baran.pack_number'),
                'menu_base_url' => config('integration.baran.menu_base_url'),
                'api_configured' => filled(config('integration.baran.api_base_url')) && filled(config('integration.baran.api_token')),
            ],
            'failedOrders' => Order::needsSync()
                ->latest()
                ->take(50)
                ->get()
                ->map(fn (Order $o) => [
                    'id' => $o->id,
                    'number' => $o->order_number,
                    'status' => $o->integration_status->value,
                    'attempts' => $o->integration_attempts,
                    'error' => $o->integration_last_error,
                    'created_at' => $o->created_at->format('Y/m/d H:i'),
                ]),
            'logs' => IntegrationLog::latest()
                ->take(60)
                ->get()
                ->map(fn (IntegrationLog $l) => [
                    'id' => $l->id,
                    'channel' => $l->channel,
                    'direction' => $l->direction,
                    'event' => $l->event,
                    'status' => $l->status,
                    'http_status' => $l->http_status,
                    'message' => $l->message,
                    'order_id' => $l->order_id,
                    'created_at' => $l->created_at?->format('Y/m/d H:i:s'),
                ]),
        ]);
    }

    public function syncCatalog(FelfeliPhotoSynchronizer $photos): RedirectResponse
    {
        try {
            SyncBaranCatalogJob::dispatchSync();
        } catch (Throwable $e) {
            IntegrationLog::create([
                'channel' => 'baran',
                'direction' => 'in',
                'event' => 'catalog.sync',
                'status' => 'failed',
                'message' => 'دریافت کاتالوگ باران ناموفق بود.',
            ]);

            return back()->with('error', 'باران در دسترس نیست و کالاها به‌روز نشدند. بعداً دوباره تلاش کنید.');
        }

        try {
            $photoSummary = $photos->sync();
        } catch (Throwable $e) {
            IntegrationLog::create([
                'channel' => 'felfeli',
                'direction' => 'in',
                'event' => 'catalog.photos.sync',
                'status' => 'failed',
                'message' => 'دریافت عکس‌های فلفلی ناموفق بود.',
            ]);

            return back()->with('error', 'کالاها و قیمت‌ها از باران به‌روز شدند، اما عکس‌های فلفلی به‌روز نشدند. دوباره تلاش کنید.');
        }

        return back()->with('success', "کالاها و قیمت‌ها از باران به‌روز شدند؛ {$photoSummary['updated']} عکس از فلفلی تازه شد.");
    }

    public function retryAll(): RedirectResponse
    {
        Artisan::call('baran:retry-orders');

        return back()->with('success', 'سفارش‌های ناموفق دوباره به صف ارسال اضافه شدند.');
    }
}
