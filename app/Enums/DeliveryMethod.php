<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Delivery = 'delivery';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Delivery => 'ارسال در ساوه',
            self::Pickup => 'دریافت حضوری',
        };
    }

    public function requiresAddress(): bool
    {
        return $this === self::Delivery;
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
