<?php

namespace App\Jobs;

use App\Enums\IntegrationStatus;
use App\Models\IntegrationLog;
use App\Models\Order;
use App\Services\Integration\IntegrationManager;
use App\Support\LogMasker;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * ارسال یک سفارش به نرم‌افزار حسابداری باران.
 *
 * ضمانت‌ها:
 *  - سفارش از قبل در دیتابیس سایت ثبت شده؛ خطای این Job هرگز باعث از دست رفتن سفارش نمی‌شود.
 *  - idempotent: اگر قبلاً synced شده، دوباره ارسال نمی‌کند.
 *  - هر تلاش در integration_logs ثبت می‌شود (با ماسک اطلاعات حساس).
 *  - پس از سقف تلاش، وضعیت روی failed می‌ماند و از پنل قابل Retry دستی است.
 */
class PushOrderToBaranJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 1; // تلاش مجدد را خودمان با backoff کنترل می‌کنیم

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return 'push-order-'.$this->orderId;
    }

    public function viaQueue(): string
    {
        return config('integration.queue', 'integration');
    }

    public function handle(IntegrationManager $manager): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (! $order || $order->integration_status === IntegrationStatus::Synced) {
            return;
        }

        $channel = $manager->orderChannel();
        $order->increment('integration_attempts');

        try {
            $result = $channel->push($order);
        } catch (Throwable $e) {
            $this->recordFailure($order, $channel->name(), $e->getMessage(), []);

            return;
        }

        IntegrationLog::create([
            'order_id' => $order->id,
            'channel' => 'baran',
            'direction' => 'out',
            'event' => 'order.push',
            'status' => $result->success ? 'success' : 'failed',
            'http_status' => $result->httpStatus,
            'request' => LogMasker::mask($result->request),
            'response' => LogMasker::mask($result->response),
            'message' => $result->message,
        ]);

        if ($result->success) {
            $order->update([
                'integration_status' => IntegrationStatus::Synced,
                'integration_synced_at' => now(),
                'integration_reference' => $result->reference,
                'integration_last_error' => null,
            ]);

            return;
        }

        $this->recordFailure($order, $channel->name(), $result->message ?? 'خطای نامشخص', $result->response);
    }

    protected function recordFailure(Order $order, string $channel, string $message, array $response): void
    {
        $maxAttempts = (int) config('integration.push.max_attempts', 5);

        $order->update([
            'integration_status' => IntegrationStatus::Failed,
            'integration_last_error' => $message,
        ]);

        // زمان‌بندی تلاش بعدی تا سقف مجاز
        if ($order->integration_attempts < $maxAttempts) {
            $backoff = config('integration.push.backoff', [60, 300, 900]);
            $delay = $backoff[min($order->integration_attempts - 1, count($backoff) - 1)] ?? 3600;

            self::dispatch($order->id)->delay(now()->addSeconds($delay));
        }
    }

    public function failed(?Throwable $e): void
    {
        IntegrationLog::create([
            'order_id' => $this->orderId,
            'channel' => 'baran',
            'direction' => 'out',
            'event' => 'order.push',
            'status' => 'failed',
            'message' => 'Job failed: '.$e?->getMessage(),
        ]);
    }
}
