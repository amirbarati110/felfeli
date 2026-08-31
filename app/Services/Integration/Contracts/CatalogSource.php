<?php

namespace App\Services\Integration\Contracts;

use App\Services\Integration\DTO\CatalogItem;

interface CatalogSource
{
    /**
     * فهرست کالاهای منبع، نرمال‌شده.
     *
     * @return iterable<CatalogItem>
     */
    public function items(): iterable;

    /** نام منبع برای لاگ‌ها */
    public function name(): string;
}
