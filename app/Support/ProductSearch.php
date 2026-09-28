<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class ProductSearch
{
    public static function apply(Builder $query, string $search): Builder
    {
        foreach (self::terms($search) as $term) {
            $query->where(function (Builder $termQuery) use ($term) {
                foreach (self::letterVariants($term) as $variant) {
                    $termQuery->orWhere('name', 'like', "%{$variant}%");
                }
            });
        }

        return $query;
    }

    public static function normalized(string $value): string
    {
        $value = str_replace(
            ['ي', 'ى', 'ك', 'ﻙ', 'ﮐ', "\u{200c}", "\u{200d}"],
            ['ی', 'ی', 'ک', 'ک', 'ک', ' ', ' '],
            mb_strtolower($value),
        );

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    /** @return list<string> */
    private static function terms(string $search): array
    {
        $normalized = self::normalized($search);

        return $normalized === '' ? [] : (preg_split('/\s+/u', $normalized) ?: []);
    }

    /** @return list<string> */
    private static function letterVariants(string $term): array
    {
        $variants = [$term];

        foreach ([['ی', 'ي'], ['ک', 'ك']] as [$persian, $arabic]) {
            $current = $variants;
            foreach ($current as $variant) {
                if (str_contains($variant, $persian)) {
                    $variants[] = str_replace($persian, $arabic, $variant);
                }
            }
        }

        return array_values(array_unique($variants));
    }
}
