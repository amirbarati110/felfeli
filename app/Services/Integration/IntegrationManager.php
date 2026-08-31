<?php

namespace App\Services\Integration;

use App\Services\Integration\Baran\BaranApiOrderChannel;
use App\Services\Integration\Baran\BaranCatalogSource;
use App\Services\Integration\Baran\BaranMenuClient;
use App\Services\Integration\Baran\SimulateOrderChannel;
use App\Services\Integration\Contracts\CatalogSource;
use App\Services\Integration\Contracts\OrderChannel;
use InvalidArgumentException;

/**
 * انتخاب درایورهای یکپارچه‌سازی بر اساس config/integration.php.
 * تنها نقطه‌ای که پیاده‌سازی‌های باران به آن گره می‌خورند.
 */
class IntegrationManager
{
    public function catalogSource(?string $driver = null): CatalogSource
    {
        $driver ??= config('integration.catalog_driver', 'baran_menu');

        return match ($driver) {
            'baran_menu' => new BaranCatalogSource(BaranMenuClient::fromConfig(), 'baran_menu'),
            'fixture' => new BaranCatalogSource(BaranMenuClient::fromConfig(), 'fixture', config('integration.fixture_path')),
            default => throw new InvalidArgumentException("درایور کاتالوگ نامعتبر: {$driver}"),
        };
    }

    public function orderChannel(?string $driver = null): OrderChannel
    {
        $driver ??= config('integration.order_driver', 'simulate');

        return match ($driver) {
            'simulate' => new SimulateOrderChannel(),
            'baran_api' => BaranApiOrderChannel::fromConfig(),
            default => throw new InvalidArgumentException("درایور سفارش نامعتبر: {$driver}"),
        };
    }
}
