<?php

namespace App\Console\Commands\Baran;

use App\Models\Order;
use App\Models\Product;
use App\Services\Integration\Baran\BaranMenuFactorOrderChannel;
use Illuminate\Console\Command;

/**
 * ⚠️ یک سفارشِ واقعی برای آزمایشِ اتصال به باران می‌سازد و از طریق
 * api/SaveMenuFactor به حسابداری می‌فرستد. یک فاکتور واقعی در باران ثبت می‌شود.
 *
 *   php artisan baran:test-order --sku=79
 */
class TestOrderCommand extends Command
{
    protected $signature = 'baran:test-order {--sku=} {--dry : فقط payload را نشان بده، ارسال نکن}';

    protected $description = 'ارسال یک سفارش تستی به باران (SaveMenuFactor)';

    public function handle(): int
    {
        $product = $this->option('sku')
            ? Product::where('sku', $this->option('sku'))->firstOrFail()
            : Product::where('in_stock', true)->where('price', '>', 0)->orderBy('price')->firstOrFail();

        $order = Order::create([
            'customer_name' => 'تست سایت فلفلی (حذف شود)',
            'customer_mobile' => '09120000000',
            'delivery_method' => 'pickup',
            'note' => 'سفارش تستیِ اتصال سایت جدید — لطفاً نادیده بگیرید یا حذف کنید.',
            'subtotal' => $product->price,
            'total' => $product->price,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ]);
        $order->refresh()->load('items');

        $this->info("سفارش تستی: {$order->order_number}  —  {$product->name}  ({$product->sku})");

        $channel = new BaranMenuFactorOrderChannel('https://api.baransys.com', '1005563', 25, 0, 0);

        $payload = \App\Services\Integration\OrderPayload::for($order);
        $this->line("\npayload:\n".json_encode($this->buildBody($order), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        if ($this->option('dry')) {
            $order->delete();

            return self::SUCCESS;
        }

        $result = $channel->push($order);

        $this->newLine();
        $this->line('success   : '.($result->success ? '✅ بله' : '❌ خیر'));
        $this->line('http      : '.$result->httpStatus);
        $this->line('شماره فاکتور: '.($result->reference ?: '—'));
        $this->line('پیام باران : '.($result->message ?: '—'));
        $this->line("\nپاسخ خام باران:\n".json_encode($result->response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        if ($result->success) {
            $order->update([
                'integration_status' => \App\Enums\IntegrationStatus::Synced,
                'integration_reference' => $result->reference,
                'integration_synced_at' => now(),
            ]);
        }

        return self::SUCCESS;
    }

    private function buildBody(Order $order): array
    {
        return [
            'PackNumber' => '1005563',
            'CustomerName' => $order->customer_name,
            'CustomerMobile' => $order->customer_mobile,
            'TableId' => 0,
            'PaymentType' => 0,
            'PaymentTypeId' => 0,
            'Description' => 'سفارش سایت — '.$order->order_number,
            'foods' => $order->items->map(fn ($i) => ['id' => (int) $i->sku, 'Count' => $i->quantity])->all(),
        ];
    }
}
