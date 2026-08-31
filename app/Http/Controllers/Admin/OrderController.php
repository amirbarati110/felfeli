<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IntegrationStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Jobs\PushOrderToBaranJob;
use App\Models\Order;
use App\Services\Integration\OrderPayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->when($request->string('q')->toString(), fn ($qr, $q) => $qr->where(fn ($w) => $w->where('order_number', 'like', "%{$q}%")->orWhere('customer_name', 'like', "%{$q}%")->orWhere('customer_mobile', 'like', "%{$q}%")))
            ->when($request->filled('status'), fn ($qr) => $qr->where('status', $request->string('status')))
            ->when($request->filled('integration'), fn ($qr) => $qr->where('integration_status', $request->string('integration')))
            ->latest()
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Order $o) => [
                'id' => $o->id,
                'number' => $o->order_number,
                'customer' => $o->customer_name,
                'mobile' => $o->customer_mobile,
                'total' => $o->total,
                'items_count' => $o->items()->count(),
                'delivery_method' => $o->delivery_method->label(),
                'status' => $o->status->value,
                'status_label' => $o->status->label(),
                'status_color' => $o->status->color(),
                'integration' => $o->integration_status->value,
                'integration_label' => $o->integration_status->label(),
                'created_at' => $o->created_at->format('Y/m/d H:i'),
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only('q', 'status', 'integration'),
            'statusOptions' => OrderStatus::options(),
            'integrationOptions' => collect(IntegrationStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()]),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load('items', 'integrationLogs');

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                ...$order->only('id', 'order_number', 'customer_name', 'customer_mobile', 'address', 'note', 'subtotal', 'delivery_fee', 'total', 'integration_attempts', 'integration_last_error', 'integration_reference'),
                'delivery_method' => $order->delivery_method->label(),
                'status' => $order->status->value,
                'integration_status' => $order->integration_status->value,
                'integration_label' => $order->integration_status->label(),
                'created_at' => $order->created_at->format('Y/m/d H:i'),
                'items' => $order->items->map(fn ($i) => $i->only('sku', 'product_name', 'unit_price', 'quantity', 'line_total')),
                'logs' => $order->integrationLogs->sortByDesc('created_at')->values()->map(fn ($l) => [
                    'event' => $l->event,
                    'status' => $l->status,
                    'message' => $l->message,
                    'http_status' => $l->http_status,
                    'created_at' => $l->created_at?->format('Y/m/d H:i:s'),
                ]),
            ],
            'payloadPreview' => OrderPayload::for($order),
            'statusOptions' => OrderStatus::options(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(OrderStatus::options()))],
        ]);

        $order->update($data);

        return back()->with('success', 'وضعیت سفارش به‌روزرسانی شد.');
    }

    public function retrySync(Order $order): RedirectResponse
    {
        if ($order->integration_status === IntegrationStatus::Synced) {
            return back()->with('error', 'این سفارش قبلاً در باران ثبت شده است.');
        }

        $order->update(['integration_status' => IntegrationStatus::Pending]);
        PushOrderToBaranJob::dispatch($order->id);

        return back()->with('success', 'سفارش دوباره به صف ارسال اضافه شد.');
    }
}
