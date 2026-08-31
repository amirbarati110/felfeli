<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\IntegrationStatus;
use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name', 'customer_mobile',
        'delivery_method', 'address', 'note',
        'subtotal', 'delivery_fee', 'total',
        'status',
        'integration_status', 'integration_attempts', 'integration_last_error',
        'integration_synced_at', 'integration_reference',
        'idempotency_key', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'delivery_method' => DeliveryMethod::class,
            'status' => OrderStatus::class,
            'integration_status' => IntegrationStatus::class,
            'subtotal' => 'integer',
            'delivery_fee' => 'integer',
            'total' => 'integer',
            'integration_attempts' => 'integer',
            'integration_synced_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->idempotency_key ??= (string) Str::uuid();
            $order->order_number ??= static::generateNumber();
        });
    }

    public static function generateNumber(): string
    {
        // FS-YYMMDD-XXXX
        do {
            $number = 'FS-'.now()->format('ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function integrationLogs(): HasMany
    {
        return $this->hasMany(IntegrationLog::class);
    }

    public function scopeNeedsSync(Builder $query): Builder
    {
        return $query->whereIn('integration_status', [
            IntegrationStatus::Pending->value,
            IntegrationStatus::Failed->value,
        ]);
    }
}
