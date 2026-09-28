<?php

namespace App\Services\Integration;

use App\Models\IntegrationLog;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FelfeliPhotoSynchronizer
{
    private const QUANTITY = '/(?<![\p{L}\d])(\d+(?:[.٫]\d+)?|یک|دو|نیم)\s*(کیلوگرمی|کیلوگرم|کیلویی|کیلو|گرمی|گرم|میلی\s*لیتر|لیتری|لیتر|گ)(?!\p{L})/u';

    public function sync(): array
    {
        $photosByName = $this->fetchPhotos();
        $photosByProduct = [];
        foreach ($photosByName as $name => $urls) {
            $key = $this->productName($name);
            if ($key !== '') {
                $photosByProduct[$key][] = ['quantity' => $this->quantity($name), 'urls' => $urls];
            }
        }
        $summary = [
            'source' => 'felfelinab.com',
            'updated' => 0,
            'manual_preserved' => 0,
            'unmatched' => 0,
            'ambiguous' => 0,
            'synced_at' => now()->toIso8601String(),
        ];

        foreach (Product::query()->get() as $product) {
            if (str_starts_with((string) $product->image, 'products/')) {
                $summary['manual_preserved']++;

                continue;
            }

            $name = $this->normalizeName($product->name);
            $matches = array_values(array_unique($photosByName[$name] ?? []));
            if ($matches === []) {
                $quantity = $this->quantity($name);
                foreach ($photosByProduct[$this->productName($name)] ?? [] as $candidate) {
                    if ($quantity !== null && $candidate['quantity'] !== null && $quantity !== $candidate['quantity']) {
                        continue;
                    }
                    $matches = array_merge($matches, $candidate['urls']);
                }
                $matches = array_values(array_unique($matches));
            }

            if ($matches === []) {
                $summary['unmatched']++;

                continue;
            }

            if (count($matches) !== 1) {
                $summary['ambiguous']++;

                continue;
            }

            if ($product->image !== $matches[0]) {
                $product->update(['image' => $matches[0]]);
                $summary['updated']++;
            }
        }

        IntegrationLog::create([
            'channel' => 'felfeli',
            'direction' => 'in',
            'event' => 'catalog.photos.sync',
            'status' => 'success',
            'request' => ['source' => $summary['source']],
            'response' => $summary,
            'message' => "عکس تازه:{$summary['updated']} دستی محفوظ:{$summary['manual_preserved']} بدون تطبیق:{$summary['unmatched']} مبهم:{$summary['ambiguous']}",
        ]);

        return $summary;
    }

    /** @return array<string,list<string>> */
    private function fetchPhotos(): array
    {
        $request = Http::baseUrl('https://felfelinab.com')->acceptJson()->timeout(20)->retry(2, 250);
        $photosByName = [];
        $totalPages = 1;

        for ($page = 1; $page <= $totalPages; $page++) {
            $response = $request->get('/wp-json/wc/store/v1/products', ['per_page' => 100, 'page' => $page])->throw();
            $rows = $response->json();

            if (! is_array($rows) || ($page === 1 && $rows === [])) {
                throw new RuntimeException('فهرست محصولات فلفلی خالی یا نامعتبر است.');
            }

            if ($page === 1) {
                $totalPages = max(1, (int) $response->header('X-WP-TotalPages', '1'));

                if ($totalPages > 20) {
                    throw new RuntimeException('تعداد صفحه‌های محصولات فلفلی بیش از حد مجاز است.');
                }
            }

            foreach ($rows as $row) {
                $name = is_array($row) ? $this->normalizeName((string) ($row['name'] ?? '')) : '';
                $url = is_array($row) ? ($row['images'][0]['src'] ?? null) : null;

                if ($name === '' || ! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
                    continue;
                }

                $photosByName[$name][] = $url;
            }
        }

        return $photosByName;
    }

    private function normalizeName(string $name): string
    {
        $name = html_entity_decode(strip_tags($name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $name = strtr(mb_strtolower($name), ['ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ة' => 'ه',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        $name = strtr($name, ['نعناع' => 'نعنا', 'خورد شده' => 'خرد شده', 'پولبیبر' => 'پول بیبر', '2 آتیشه' => 'دو آتیشه']);

        return trim(preg_replace('/[\s\x{200C}\x{200D}]+/u', ' ', $name) ?? '');
    }

    /** Remove package quantities only; retain flavour, brand and preparation qualifiers. */
    private function productName(string $name): string
    {
        $name = preg_replace(self::QUANTITY, ' ', $name) ?? $name;
        $name = preg_replace('/[()\[\]،,\-]/u', ' ', $name) ?? $name;
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        sort($words, SORT_STRING);

        return implode(' ', $words);
    }

    private function quantity(string $name): ?string
    {
        if (! preg_match(self::QUANTITY, $name, $parts)) {
            return null;
        }
        $amount = (float) strtr($parts[1], ['یک' => '1', 'دو' => '2', 'نیم' => '0.5', '٫' => '.']);
        $unit = $parts[2];
        if (str_starts_with($unit, 'کیلو')) {
            return ($amount * 1000).'g';
        }
        if (str_contains($unit, 'لیتر')) {
            return ($amount * (str_starts_with($unit, 'میلی') ? 1 : 1000)).'ml';
        }

        return $amount.'g';
    }
}
