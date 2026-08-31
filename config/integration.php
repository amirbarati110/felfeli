<?php

return [

    /*
    |--------------------------------------------------------------------------
    | یکپارچه‌سازی با نرم‌افزار حسابداری باران
    |--------------------------------------------------------------------------
    |
    | این پروژه به‌صورت «آداپتور» ساخته شده است: منطق فروشگاه به باران وابسته
    | نیست و سفارش‌ها همیشه اول در دیتابیس سایت ثبت می‌شوند. ارسال به باران از
    | طریق صف انجام می‌شود و در صورت خطا قابل تلاش مجدد است.
    |
    | تا زمان دریافت مستندات رسمی API باران (به‌ویژه endpoint ثبت سفارش و روش
    | احراز هویت)، درایور کاتالوگ روی حالت واقعیِ «منوی عمومی باران» و درایور
    | سفارش روی حالت «شبیه‌سازی» کار می‌کند.
    |
    */

    // baran_menu = خواندن از API عمومی منوی باران (کشف‌شده، بدون احراز هویت)
    // fixture    = خواندن از فایل نمونه‌ی ذخیره‌شده (برای توسعه/تست بدون شبکه)
    'catalog_driver' => env('INTEGRATION_CATALOG_DRIVER', 'baran_menu'),

    // baran_api  = ارسال واقعی به API باران (نیازمند مستندات و توکن)
    // simulate   = شبیه‌سازی موفق با شماره‌ی فاکتور ساختگی (پیش‌فرض فعلی)
    // null/log   = فقط لاگ می‌کند و سفارش در وضعیت pending می‌ماند
    'order_driver' => env('INTEGRATION_ORDER_DRIVER', 'simulate'),

    'baran' => [
        // شماره‌ی پک منوی باران — فروشگاه فلفلی شعبه ساوه
        'pack_number' => env('BARAN_PACK_NUMBER', '1005563'),

        // پایه‌ی API عمومی منو
        'menu_base_url' => env('BARAN_MENU_BASE_URL', 'https://api.baransys.com'),

        // پایه و توکن API حسابداری (تکمیل پس از دریافت مستندات باران)
        'api_base_url' => env('BARAN_API_BASE_URL'),
        'api_token' => env('BARAN_API_TOKEN'),

        'timeout' => (int) env('BARAN_HTTP_TIMEOUT', 20),
        'retries' => (int) env('BARAN_HTTP_RETRIES', 2),
    ],

    // مسیر فایل نمونه برای درایور fixture
    'fixture_path' => database_path('fixtures/baran-settings-and-products.json'),

    // صف اختصاصی کارهای یکپارچه‌سازی
    'queue' => env('INTEGRATION_QUEUE', 'integration'),

    // سیاست تلاش مجدد برای ارسال سفارش
    'push' => [
        'max_attempts' => (int) env('INTEGRATION_PUSH_MAX_ATTEMPTS', 5),
        'backoff' => [60, 300, 900, 3600, 10800], // ثانیه
    ],

    // کلیدهایی که در لاگ‌ها ماسک می‌شوند
    'log_mask_keys' => [
        'mobile', 'customer_mobile', 'phone', 'address', 'token',
        'api_token', 'authorization', 'password',
    ],
];
