<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IntegrationStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'orders_new' => Order::where('status', OrderStatus::New)->count(),
                'sync_failed' => Order::where('integration_status', IntegrationStatus::Failed)->count(),
                'sync_pending' => Order::where('integration_status', IntegrationStatus::Pending)->count(),
                'products_total' => Product::count(),
                'products_out' => Product::where('in_stock', false)->count(),
            ],
            'recentOrders' => Order::latest()->take(8)->get()->map(fn (Order $o) => [
                'id' => $o->id,
                'number' => $o->order_number,
                'customer' => $o->customer_name,
                'total' => $o->total,
                'status' => $o->status->value,
                'status_label' => $o->status->label(),
                'integration' => $o->integration_status->value,
                'created_at' => $o->created_at->diffForHumans(),
            ]),
        ]);
    }
}
