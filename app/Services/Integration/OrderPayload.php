<?php

namespace App\Services\Integration;

use App\Models\Order;

/**
 * Payload حداقلیِ سفارش طبق PRD (بخش ۱۱) — ورودیِ ثبت در نرم‌افزار حسابداری.
 * مچ اقلام فقط بر مبنای sku (کد کالای انبار) انجام می‌شود، نه نام.
 */
class OrderPayload
{
    public static function for(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'order_id' => $order->order_number,
            'idempotency_key' => $order->idempotency_key,
            'created_at' => $order->created_at?->toIso8601String(),

            'customer_name' => $order->customer_name,
            'mobile' => $order->customer_mobile,
            'delivery_method' => $order->delivery_method->value,
            'address' => $order->delivery_method->requiresAddress() ? $order->address : null,
            'customer_note' => $order->note,

            'items' => $order->items->map(fn ($item) => [
                'product_id' => $item->sku,
                'sku' => $item->sku,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->all(),

            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'order_total' => $order->total,
        ];
    }
}
