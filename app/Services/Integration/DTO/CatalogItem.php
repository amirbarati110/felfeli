<?php

namespace App\Services\Integration\DTO;

/**
 * یک قلم کالا که از منبع بیرونی (باران) خوانده می‌شود، نرمال‌شده و مستقل از
 * ساختار پاسخ منبع. مبنای upsert در سایت، فیلد sku است.
 */
final readonly class CatalogItem
{
    public function __construct(
        public string $sku,
        public string $name,
        public int $price,               // ریال، صحیح
        public bool $inStock,
        public ?int $stockQty = null,
        public ?string $imageUrl = null,
        public ?string $description = null,
        public ?string $sourceCategory = null,
        public ?int $comparePrice = null,
        public array $raw = [],           // پاسخ خام برای عیب‌یابی
    ) {}

    /** @param array<string,mixed> $row */
    public static function fromBaranMenuRow(array $row, ?string $category = null): self
    {
        $sale = (int) round((float) ($row['salePrice'] ?? 0));
        $regular = (int) round((float) ($row['regularPrice'] ?? 0));
        $price = $sale > 0 ? $sale : $regular;
        $remain = (int) round((float) ($row['ProductRemainCount'] ?? 0));

        $image = $row['image'] ?? null;
        if (is_string($image) && $image !== '') {
            $image = preg_replace('#(?<!:)//+#', '/', $image);
        } else {
            $image = null;
        }

        return new self(
            sku: (string) ($row['id'] ?? ''),
            name: trim((string) ($row['title'] ?? '')),
            price: $price,
            inStock: $remain > 0,
            stockQty: $remain,
            imageUrl: $image,
            description: filled($row['description'] ?? null) ? (string) $row['description'] : null,
            sourceCategory: $category,
            comparePrice: ($regular > 0 && $sale > 0 && $regular > $sale) ? $regular : null,
            raw: $row,
        );
    }

    public function isValid(): bool
    {
        return $this->sku !== '' && $this->name !== '';
    }
}
