<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminCatalogRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_admin_refresh_updates_baran_product_data_then_its_felfeli_photo(): void
    {
        config()->set('integration.catalog_driver', 'baran_menu');

        Http::fake([
            '*SettingsAndProducts*' => Http::response([
                'foods' => [[
                    'category' => 'نمونه',
                    'data' => [[
                        'id' => '301',
                        'title' => 'روغن زیتون',
                        'salePrice' => 12000,
                        'regularPrice' => 12000,
                        'ProductRemainCount' => 7,
                        'image' => 'https://baran.example/should-not-be-used.jpg',
                    ]],
                ]],
            ]),
            'felfelinab.com/wp-json/wc/store/v1/products*' => Http::response([
                ['name' => 'روغن زیتون', 'images' => [['src' => 'https://felfelinab.com/olive.jpg']]],
            ], 200, ['X-WP-TotalPages' => '1']),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.integration.sync'))
            ->assertRedirect();

        $product = Product::where('sku', '301')->firstOrFail();
        $this->assertSame(12000, $product->price);
        $this->assertSame(7, $product->stock_qty);
        $this->assertSame('https://felfelinab.com/olive.jpg', $product->image);
    }

    public function test_admin_sees_partial_failure_when_felfeli_photos_cannot_be_fetched(): void
    {
        config()->set('integration.catalog_driver', 'baran_menu');

        Http::fake([
            '*SettingsAndProducts*' => Http::response([
                'foods' => [[
                    'category' => 'نمونه',
                    'data' => [[
                        'id' => '302',
                        'title' => 'روغن کنجد',
                        'salePrice' => 15000,
                        'ProductRemainCount' => 4,
                    ]],
                ]],
            ]),
            'felfelinab.com/wp-json/wc/store/v1/products*' => Http::response([], 503),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.integration.sync'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['sku' => '302', 'price' => 15000]);
    }

    public function test_admin_sees_a_clear_error_when_baran_catalog_is_unavailable(): void
    {
        config()->set('integration.catalog_driver', 'baran_menu');
        Http::fake(['*SettingsAndProducts*' => Http::response([], 503)]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.integration.sync'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, Product::count());
    }
}
