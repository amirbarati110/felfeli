<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_search_matches_a_persian_prefix_against_an_arabic_product_name(): void
    {
        $this->product('101', 'ادويه ترشي');
        $this->product('102', 'روغن کنجد');

        $this->get(route('menu.index', ['q' => 'ادوی']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Menu/Index', false)
                ->has('products.data', 1)
                ->where('products.data.0.sku', '101'));
    }

    public function test_search_suggestions_return_partial_matches_and_are_limited_to_eight(): void
    {
        foreach (range(1, 10) as $i) {
            $this->product((string) $i, "فلفل محصول {$i}");
        }

        $this->getJson(route('menu.suggestions', ['q' => 'فلف']))
            ->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('data.0.name', 'فلفل محصول 1')
            ->assertJsonStructure(['data' => [['sku', 'name', 'price', 'image_url']]]);
    }

    public function test_search_suggestions_require_two_characters(): void
    {
        $this->product('101', 'فلفل قرمز');

        $this->getJson(route('menu.suggestions', ['q' => 'ف']))
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    private function product(string $sku, string $name): Product
    {
        return Product::query()->create([
            'sku' => $sku,
            'name' => $name,
            'price' => 125000,
            'in_stock' => true,
            'is_active' => true,
            'source' => 'baran',
        ]);
    }
}
