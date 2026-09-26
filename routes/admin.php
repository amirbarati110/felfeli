<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
| پنل مدیریت فروشگاه فلفلی ساوه
*/

$adminDomain = config('app.admin_domain');

Route::group([
    'domain' => $adminDomain,
    'prefix' => $adminDomain ? '' : 'admin',
    'as' => 'admin.',
], function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('products', ProductController::class)->except('show')->scoped(['product' => 'id']);
        Route::post('products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');

        Route::resource('categories', CategoryController::class)->except('show');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/retry-sync', [OrderController::class, 'retrySync'])->name('orders.retry');

        Route::get('integration', [IntegrationController::class, 'index'])->name('integration.index');
        Route::post('integration/sync-catalog', [IntegrationController::class, 'syncCatalog'])->name('integration.sync');
        Route::post('integration/retry-all', [IntegrationController::class, 'retryAll'])->name('integration.retryAll');

        Route::get('import', [ProductImportController::class, 'index'])->name('import.index');
        Route::post('import', [ProductImportController::class, 'store'])->name('import.store');
        Route::get('import/template/download', [ProductImportController::class, 'template'])->name('import.template');
        Route::get('import/{productImport}', [ProductImportController::class, 'show'])->name('import.show');

        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
