<?php

namespace App\Services\Integration\Baran;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Integration\Contracts\OrderChannel;
use App\Services\Integration\DTO\OrderPushResult;
use Illuminate\Support\Facades\Http;

/**
 * ثبت سفارش در نرم‌افزار حسابداری باران از طریق همان endpointی که «منوی آنلاین
 * باران» استفاده می‌کند — بدون نیاز به API اختصاصی از باران.
 *
 *   POST https://api.baransys.com/api/SaveMenuFactor
 *   body: { PackNumber, CustomerName, CustomerMobile, TableId,
 *           PaymentType (0=حضوری/نقدی، 1=آنلاین), PaymentTypeId,
 *           Description, foods: [{ id, Count }] }
 *   پاسخ: { ResId, ResMessage, FactorId }
 *          ResId < 0            ⇒ خطا (متن در ResMessage)
 *          ResId truthy & FactorId ⇒ موفق (شماره‌ی فاکتور = ResId ، شناسه = FactorId)
 *
 * توجه: مچ اقلام با فیلد `id` (همان id کالای منوی باران = sku ما) انجام می‌شود.
 * آدرس ارسال در این payload جای مستقلی ندارد و داخل Description قرار می‌گیرد.
 */
class BaranMenuFactorOrderChannel implements OrderChannel
{
    public function __construct(
        protected string $baseUrl,
        protected string $packNumber,
        protected int $timeout = 20,
        protected int $tableId = 0,
        protected int $paymentTypeId = 0,
    ) {}

    public static function fromConfig(): self
    {
        $store = Setting::get('store', []);

        return new self(
            baseUrl: rtrim((string) config('integration.baran.menu_base_url'), '/'),
            packNumber: (string) config('integration.baran.pack_number'),
            timeout: (int) config('integration.baran.timeout', 20),
            tableId: (int) config('integration.baran.table_id', 0),
            paymentTypeId: (int) ($store['web_payment_type_id'] ?? config('integration.baran.payment_type_id', 0)),
        );
    }

    public function name(): string
    {
        return 'baran:menu_factor';
    }

    public function push(Order $order): OrderPushResult
    {
        $order->loadMissing('items');

        $payload = [
            'PackNumber' => $this->packNumber,
            'CustomerName' => $order->customer_name,
            'CustomerMobile' => $order->customer_mobile,
            'TableId' => $this->tableId,
            'PaymentType' => 0, // همیشه حضوری/نقدی — فروشگاه پرداخت آنلاین ندارد
            'PaymentTypeId' => $this->paymentTypeId,
            'Description' => $this->buildDescription($order),
            'foods' => $order->items->map(fn ($item) => [
                'id' => (int) $item->sku,
                'Count' => $item->quantity,
            ])->values()->all(),
        ];

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Referer' => 'https://menu.baransys.com/',
                'Origin' => 'https://menu.baransys.com',
            ])
            ->post('/api/SaveMenuFactor', $payload);

        $body = (array) $response->json();
        $resId = $body['ResId'] ?? null;

        if (! $response->successful()) {
            return OrderPushResult::fail('HTTP '.$response->status().' از باران', $payload, $body, $response->status());
        }

        if ($resId !== null && (int) $resId < 0) {
            return OrderPushResult::fail($body['ResMessage'] ?? 'باران سفارش را رد کرد', $payload, $body, $response->status());
        }

        if (empty($resId) && empty($body['FactorId'])) {
            return OrderPushResult::fail($body['ResMessage'] ?? 'پاسخ نامشخص از باران', $payload, $body, $response->status());
        }

        return OrderPushResult::ok(
            reference: (string) ($body['ResId'] ?? $body['FactorId']),
            request: $payload,
            response: $body,
            http: $response->status(),
        );
    }

    protected function buildDescription(Order $order): string
    {
        $lines = ['سفارش سایت — '.$order->order_number];
        $lines[] = 'روش دریافت: '.$order->delivery_method->label();

        if ($order->delivery_method->requiresAddress() && filled($order->address)) {
            $lines[] = 'آدرس: '.$order->address;
        }

        if (filled($order->note)) {
            $lines[] = 'یادداشت مشتری: '.$order->note;
        }

        return implode("\n", $lines);
    }
}
