<?php

namespace App\Enums;

enum IntegrationStatus: string
{
    case Pending = 'pending';
    case Synced = 'synced';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در صف ارسال',
            self::Synced => 'ثبت‌شده در باران',
            self::Failed => 'خطا در ارسال',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Synced => 'success',
            self::Failed => 'danger',
        };
    }
}
