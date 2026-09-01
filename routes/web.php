<?php

use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\LandingController;
use App\Http\Controllers\Shop\MenuController;
use App\Http\Controllers\Shop\OrderController;
use Illuminate\Support\Facades\Route;

/*
| فروشگاه فلفلی ساوه — منوی دیجیتال و ثبت سفارش
| اصل PRD: کمترین کلیک از ورود تا ثبت سفارش، بدون ورود کاربر.
*/

Route::get('/', LandingController::class)->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');

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

require __DIR__.'/admin.php';
