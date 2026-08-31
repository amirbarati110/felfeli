<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Reviewed = 'reviewed';
    case Contacted = 'contacted';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'جدید',
            self::Reviewed => 'بررسی‌شده',
            self::Contacted => 'تماس گرفته شد',
            self::Confirmed => 'تأییدشده',
            self::Preparing => 'آماده‌سازی',
            self::Shipped => 'ارسال‌شده',
            self::Delivered => 'تحویل‌شده',
            self::Canceled => 'لغوشده',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Reviewed, self::Contacted => 'warning',
            self::Confirmed, self::Preparing => 'primary',
            self::Shipped => 'purple',
            self::Delivered => 'success',
            self::Canceled => 'danger',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
