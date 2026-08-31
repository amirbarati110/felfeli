<?php

namespace App\Support;

class LogMasker
{
    /**
     * ماسک‌کردن مقادیر حساس در آرایه‌ی تودرتو، پیش از ذخیره در لاگ.
     *
     * @param  array<string,mixed>  $data
     * @param  list<string>|null  $keys
     * @return array<string,mixed>
     */
    public static function mask(array $data, ?array $keys = null): array
    {
        $keys = array_map('strtolower', $keys ?? config('integration.log_mask_keys', []));

        array_walk_recursive($data, function (&$value, $key) use ($keys) {
            if (is_string($key) && in_array(strtolower($key), $keys, true) && is_scalar($value)) {
                $value = self::maskValue((string) $value);
            }
        });

        return $data;
    }

    public static function maskValue(string $value): string
    {
        $len = mb_strlen($value);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return mb_substr($value, 0, 2).str_repeat('*', max(3, $len - 4)).mb_substr($value, -2);
    }
}
