<?php

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\LandingController;
use App\Http\Controllers\Shop\MenuController;
use App\Http\Controllers\Shop\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
| فروشگاه فلفلی ساوه — منوی دیجیتال و ثبت سفارش
| اصل PRD: کمترین کلیک از ورود تا ثبت سفارش، بدون ورود کاربر.
*/

// Register the domain-specific admin routes first so / on the admin host wins.
require __DIR__.'/admin.php';

Route::get('/', LandingController::class)->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

Route::get('/product-images/{filename}', function (string $filename) {
    $path = "products/{$filename}";
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path, null, ['Cache-Control' => 'public, max-age=86400']);
})->where('filename', '[A-Za-z0-9_-]+\\.(?:jpe?g|png|webp|gif|avif|bmp)')->name('products.image');

Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'add')->name('add');
    Route::match(['put', 'patch'], '/{sku}', 'update')->name('update');
    Route::delete('/{sku}', 'remove')->name('remove');
});

Route::controller(CheckoutController::class)->group(function () {
    Route::get('/checkout', 'show')->name('checkout.show');
    Route::post('/checkout', 'store')->name('checkout.store')->middleware('throttle:20,1');
});

Route::get('/order/{orderNumber}', [OrderController::class, 'show'])->name('order.show');
