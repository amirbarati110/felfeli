<?php

namespace App\Observers;

use App\Jobs\PushOrderToBaranJob;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        // ارسال به باران فقط از طریق صف — مستقل از در دسترس بودن حسابداری
        PushOrderToBaranJob::dispatch($order->id)->afterCommit();
    }
}
