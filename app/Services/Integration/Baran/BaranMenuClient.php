<?php

namespace App\Services\Integration\Baran;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * کلاینت API عمومیِ «منوی آنلاین باران».
 *
 * endpoint کشف‌شده:
 *   GET {menu_base_url}/api/SettingsAndProducts?Packnumber={pack}
 *
 * بدون احراز هویت. پاسخ شامل settings (تنظیمات فروشگاه)، tables و
 * foods (گروه‌های دسته‌بندی به همراه اقلام) است.
 *
 * توجه: این «نمای منو» است. API حسابداریِ باران (برای کد کالا/بارکد و ثبت
 * فاکتور) جداست و پس از دریافت مستندات به BaranApiClient اضافه می‌شود.
 */
class BaranMenuClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $packNumber,
        protected int $timeout = 20,
        protected int $retries = 2,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: rtrim((string) config('integration.baran.menu_base_url'), '/'),
            packNumber: (string) config('integration.baran.pack_number'),
            timeout: (int) config('integration.baran.timeout', 20),
            retries: (int) config('integration.baran.retries', 2),
        );
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->retry($this->retries, 500, throw: false)
            ->acceptJson();
    }

    /** پاسخ کامل settings + products */
    public function settingsAndProducts(): array
    {
        return $this->request()
            ->get('/api/SettingsAndProducts', ['Packnumber' => $this->packNumber])
            ->throw()
            ->json() ?? [];
    }
}
