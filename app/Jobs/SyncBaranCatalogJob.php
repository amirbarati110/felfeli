<?php

namespace App\Jobs;

use App\Services\Integration\CatalogSynchronizer;
use App\Services\Integration\IntegrationManager;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * همگام‌سازی دوره‌ای کاتالوگ از باران (قیمت/موجودی/نام؛ بدون عکس).
 * توسط زمان‌بند اجرا می‌شود؛ بازه پس از تأیید باران (real-time یا دوره‌ای) نهایی می‌شود.
 */
class SyncBaranCatalogJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $uniqueFor = 600;

    public function __construct(public ?string $driver = null, public bool $deactivateMissing = true) {}

    public function viaQueue(): string
    {
        return config('integration.queue', 'integration');
    }

    public function handle(IntegrationManager $manager): void
    {
        $source = $manager->catalogSource($this->driver);

        (new CatalogSynchronizer($source))->sync($this->deactivateMissing);
    }
}
