<?php

namespace App\Console\Commands\Baran;

use App\Jobs\PushOrderToBaranJob;
use App\Models\Order;
use Illuminate\Console\Command;

class RetryOrdersCommand extends Command
{
    protected $signature = 'baran:retry-orders {--id=* : فقط این شماره‌سفارش‌ها}';

    protected $description = 'ارسال مجدد سفارش‌های در وضعیت pending/failed به باران';

    public function handle(): int
    {
        $query = Order::query()->needsSync();

        if ($ids = $this->option('id')) {
            $query->whereIn('order_number', $ids);
        }

        $count = 0;
        $query->each(function (Order $order) use (&$count) {
            PushOrderToBaranJob::dispatch($order->id);
            $count++;
        });

        $this->info("{$count} سفارش به صف ارسال اضافه شد.");

        return self::SUCCESS;
    }
}
