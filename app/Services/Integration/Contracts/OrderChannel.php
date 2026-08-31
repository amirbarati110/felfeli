<?php

namespace App\Services\Integration\Contracts;

use App\Models\Order;
use App\Services\Integration\DTO\OrderPushResult;

interface OrderChannel
{
    /**
     * ارسال یک سفارش ثبت‌شده به نرم‌افزار حسابداری.
     * پیاده‌سازی باید idempotent باشد (کلید: idempotency_key سفارش).
     */
    public function push(Order $order): OrderPushResult;

    public function name(): string;
}
