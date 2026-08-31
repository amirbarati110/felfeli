<?php

namespace App\Services\Integration\Baran;

use App\Models\Order;
use App\Services\Integration\Contracts\OrderChannel;
use App\Services\Integration\DTO\OrderPushResult;
use App\Services\Integration\OrderPayload;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * درایور واقعی ارسال سفارش به API حسابداری باران.
 *
 * ⚠️ تکمیل‌نشده — منتظر مستندات باران:
 *   - آدرس دقیق endpoint ثبت پیش‌فاکتور/فاکتور
 *   - روش احراز هویت (توکن / کلید / یوزر-پس)
 *   - نگاشت فیلدها و نحوه‌ی مچ کد کالا
 *   - آیا وضعیت سفارش از باران بازمی‌گردد؟
 *
 * تا آن زمان BaranApiClient::createOrder استثنا پرتاب می‌کند و درایور پیش‌فرض
 * simulate است (config/integration.php).
 */
class BaranApiOrderChannel implements OrderChannel
{
    public function __construct(
        protected ?string $baseUrl,
        protected ?string $token,
        protected int $timeout = 20,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: config('integration.baran.api_base_url'),
            token: config('integration.baran.api_token'),
            timeout: (int) config('integration.baran.timeout', 20),
        );
    }

    public function name(): string
    {
        return 'baran:api';
    }

    public function push(Order $order): OrderPushResult
    {
        if (blank($this->baseUrl) || blank($this->token)) {
            throw new RuntimeException(
                'اتصال به API حسابداری باران پیکربندی نشده است (BARAN_API_BASE_URL / BARAN_API_TOKEN). '
                .'درایور فعلی را روی simulate بگذارید یا مستندات باران را تکمیل کنید.'
            );
        }

        $payload = OrderPayload::for($order);

        $response = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->timeout($this->timeout)
            ->withToken($this->token)
            ->withHeaders(['Idempotency-Key' => $order->idempotency_key])
            ->acceptJson()
            // نام endpoint موقتی است — پس از دریافت مستندات اصلاح شود
            ->post('/api/orders', $payload);

        if ($response->successful()) {
            return OrderPushResult::ok(
                reference: (string) ($response->json('invoice_number') ?? $response->json('id') ?? ''),
                request: $payload,
                response: (array) $response->json(),
                http: $response->status(),
            );
        }

        return OrderPushResult::fail(
            message: 'باران خطا برگرداند: HTTP '.$response->status(),
            request: $payload,
            response: (array) $response->json(),
            http: $response->status(),
        );
    }
}
