<?php

namespace App\Services\Integration\Baran;

use App\Models\Order;
use App\Services\Integration\Contracts\OrderChannel;
use App\Services\Integration\DTO\OrderPushResult;
use App\Services\Integration\OrderPayload;

/**
 * درایور شبیه‌سازی — تا زمان دریافت مستندات endpoint ثبت سفارش باران.
 *
 * رفتار: payload کامل ساخته می‌شود، لاگ می‌شود، و یک شماره‌ی «سند» ساختگی
 * برگردانده می‌شود تا کل جریان (صف، وضعیت synced، نمایش در پنل، Retry) قابل
 * تست باشد. با تنظیم INTEGRATION_ORDER_DRIVER=baran_api و تکمیل BaranApiClient
 * جایگزین می‌شود.
 */
class SimulateOrderChannel implements OrderChannel
{
    public function name(): string
    {
        return 'baran:simulate';
    }

    public function push(Order $order): OrderPushResult
    {
        $payload = OrderPayload::for($order);

        // شبیه‌سازی خطای گاه‌به‌گاه برای تست مسیر Retry (غیرفعال به‌صورت پیش‌فرض)
        if (config('integration.simulate_failures') && random_int(1, 5) === 1) {
            return OrderPushResult::fail('شبیه‌سازی خطای موقت باران', $payload);
        }

        return OrderPushResult::ok(
            reference: 'SIM-'.now()->format('ymd').'-'.$order->id,
            request: $payload,
            response: ['simulated' => true, 'accepted_at' => now()->toIso8601String()],
        );
    }
}
