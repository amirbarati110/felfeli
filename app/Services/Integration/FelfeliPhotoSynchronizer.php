<?php

namespace App\Services\Integration;

use App\Models\IntegrationLog;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FelfeliPhotoSynchronizer
{
    public function sync(): array
    {
        $photosByName = $this->fetchPhotos();
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

            $matches = $photosByName[$this->normalizeName($product->name)] ?? [];

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
        $name = strtr(mb_strtolower($name), ['ي' => 'ی', 'ك' => 'ک', 'ة' => 'ه']);

        return trim(preg_replace('/[\s\x{200C}\x{200D}]+/u', ' ', $name) ?? '');
    }
}
