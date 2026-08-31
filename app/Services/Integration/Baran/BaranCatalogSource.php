<?php

namespace App\Services\Integration\Baran;

use App\Services\Integration\Contracts\CatalogSource;
use App\Services\Integration\DTO\CatalogItem;
use Generator;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * منبع کاتالوگ از منوی باران.
 *
 * دو حالت:
 *  - baran_menu : فراخوانی زنده‌ی API
 *  - fixture    : خواندن از فایل نمونه‌ی ذخیره‌شده (توسعه/تست آفلاین)
 */
class BaranCatalogSource implements CatalogSource
{
    public function __construct(
        protected BaranMenuClient $client,
        protected string $mode = 'baran_menu',
        protected ?string $fixturePath = null,
    ) {}

    public function name(): string
    {
        return "baran:{$this->mode}";
    }

    /** @return Generator<CatalogItem> */
    public function items(): iterable
    {
        $payload = $this->payload();

        foreach ($payload['foods'] ?? [] as $group) {
            $category = $group['category'] ?? null;

            foreach ($group['data'] ?? [] as $row) {
                $item = CatalogItem::fromBaranMenuRow($row, $category);

                if ($item->isValid()) {
                    yield $item;
                }
            }
        }
    }

    /** تنظیمات فروشگاه از پاسخ باران (نام، لوگو، تلفن، ساعت کاری، ...) */
    public function storeSettings(): array
    {
        return $this->payload()['settings'] ?? [];
    }

    protected function payload(): array
    {
        if ($this->mode === 'fixture') {
            $path = $this->fixturePath ?? config('integration.fixture_path');

            if (! $path || ! File::exists($path)) {
                throw new RuntimeException("فایل نمونه‌ی باران یافت نشد: {$path}");
            }

            return json_decode(File::get($path), true) ?: [];
        }

        return $this->client->settingsAndProducts();
    }
}
