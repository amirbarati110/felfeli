<?php

namespace App\Providers;

use App\Services\Cart\Cart;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Cart::class, fn ($app) => new Cart($app['session.store']));
    }

    public function boot(): void
    {
        //
    }
}
