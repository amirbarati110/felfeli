<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Cart\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(protected Cart $cart) {}

    /**
     * ثبت سفارش از روی سبد. همه‌ی مبالغ سمت سرور بازمحاسبه می‌شوند.
     *
     * @param  array{customer_name:string,customer_mobile:string,delivery_method:string,address:?string,note:?string}  $data
     *
     * @throws ValidationException وقتی سبد خالی/نامعتبر است یا کالایی ناموجود شده
     */
    public function place(array $data): Order
    {
        $snapshot = $this->cart->snapshot();

        if ($snapshot['removed']) {
            throw ValidationException::withMessages([
                'cart' => 'برخی کالاها ناموجود شدند و از سبد حذف شدند: '.implode('، ', $snapshot['removed']).'. لطفاً سبد را بازبینی کنید.',
            ]);
        }

        if ($snapshot['items']->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'سبد خرید خالی است.']);
        }

        $method = DeliveryMethod::from($data['delivery_method']);

        $checkout = Setting::get('checkout', []);
        $deliveryFee = $method === DeliveryMethod::Delivery ? (int) ($checkout['delivery_fee'] ?? 0) : 0;
        $subtotal = (int) $snapshot['subtotal'];
        $minOrder = (int) ($checkout['min_order_total'] ?? 0);

        if ($minOrder > 0 && $subtotal < $minOrder) {
            throw ValidationException::withMessages([
                'cart' => 'حداقل مبلغ سفارش '.number_format($minOrder).' ریال است.',
            ]);
        }

        return DB::transaction(function () use ($data, $method, $snapshot, $subtotal, $deliveryFee) {
            // قفل کالاها و بازبینی نهاییِ قیمت/موجودی
            $skus = $snapshot['items']->pluck('sku')->all();
            $products = Product::query()->whereIn('sku', $skus)->lockForUpdate()->get()->keyBy('sku');

            $order = new Order([
                'customer_name' => trim($data['customer_name']),
                'customer_mobile' => $this->normalizeMobile($data['customer_mobile']),
                'delivery_method' => $method->value,
                'address' => $method->requiresAddress() ? trim((string) $data['address']) : null,
                'note' => filled($data['note'] ?? null) ? trim($data['note']) : null,
                'delivery_fee' => $deliveryFee,
            ]);

            $computedSubtotal = 0;
            $lines = [];

            foreach ($snapshot['items'] as $line) {
                /** @var Product $product */
                $product = $products->get($line['sku']);

                if (! $product || ! $product->isOrderable()) {
                    throw ValidationException::withMessages([
                        'cart' => "کالای «{$line['name']}» ناموجود شد. لطفاً سبد را بازبینی کنید.",
                    ]);
                }

                $qty = (int) $line['quantity'];
                if ($product->track_stock && $product->stock_qty !== null) {
                    $qty = min($qty, max(1, $product->stock_qty));
                }

                $lineTotal = $product->price * $qty;
                $computedSubtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ];
            }

            $order->subtotal = $computedSubtotal;
            $order->total = $computedSubtotal + $deliveryFee;
            $order->save();

            $order->items()->createMany($lines);

            $this->cart->clear();

            return $order->load('items');
        });
    }

    protected function normalizeMobile(string $mobile): string
    {
        $mobile = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', ' ', '-'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '', ''],
            $mobile,
        );

        if (str_starts_with($mobile, '+98')) {
            $mobile = '0'.substr($mobile, 3);
        } elseif (str_starts_with($mobile, '98') && strlen($mobile) === 12) {
            $mobile = '0'.substr($mobile, 2);
        }

        return $mobile;
    }
}
