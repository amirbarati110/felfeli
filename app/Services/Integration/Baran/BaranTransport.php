<?php

namespace App\Services\Integration\Baran;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * لایه‌ی انتقالِ مقاوم برای API باران.
 *
 * روی برخی شبکه‌ها/هاست‌های ایران، cURL نمی‌تواند TLS با api.baransys.com
 * برقرار کند (خطای «wrong version number») در حالی که استریمِ بومیِ PHP کار
 * می‌کند. این کلاس اول با کلاینت HTTP لاراول تلاش می‌کند و در صورت خطای
 * اتصال، به stream context بومی برمی‌گردد.
 *
 * @return array{status:int, body:string, json:mixed}
 */
class BaranTransport
{
    public static function get(string $url, array $query = [], int $timeout = 20): array
    {
        $full = $url.($query ? '?'.http_build_query($query) : '');

        try {
            $r = Http::timeout($timeout)->acceptJson()->get($url, $query);

            return self::wrap($r->status(), $r->body());
        } catch (ConnectionException $e) {
            return self::stream('GET', $full, null, ['Accept: application/json'], $timeout, $e);
        }
    }

    public static function postJson(string $url, array $payload, array $headers = [], int $timeout = 20): array
    {
        try {
            $r = Http::timeout($timeout)->withHeaders($headers)->acceptJson()->asJson()->post($url, $payload);

            return self::wrap($r->status(), $r->body());
        } catch (ConnectionException $e) {
            $hdr = ['Content-Type: application/json', 'Accept: application/json'];
            foreach ($headers as $k => $v) {
                $hdr[] = "{$k}: {$v}";
            }

            return self::stream('POST', $url, json_encode($payload, JSON_UNESCAPED_UNICODE), $hdr, $timeout, $e);
        }
    }

    private static function stream(string $method, string $url, ?string $body, array $headers, int $timeout, \Throwable $primary): array
    {
        $ctx = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $body ?? '',
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);

        $raw = @file_get_contents($url, false, $ctx);

        if ($raw === false) {
            throw new RuntimeException(
                'اتصال به باران ناموفق بود (هم cURL و هم stream). خطای اصلی: '.$primary->getMessage()
            );
        }

        $status = 0;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
                $status = (int) $m[1];
            }
        }

        return self::wrap($status ?: 200, $raw);
    }

    private static function wrap(int $status, string $body): array
    {
        return [
            'status' => $status,
            'body' => $body,
            'json' => json_decode($body, true),
        ];
    }
}
