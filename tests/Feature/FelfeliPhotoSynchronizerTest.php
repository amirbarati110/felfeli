<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Integration\FelfeliPhotoSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FelfeliPhotoSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_sizes_match_but_distinct_photos_and_product_qualifiers_are_not_guessed(): void
    {
        $spice = Product::create(['sku' => '94', 'name' => 'ادويه آبگوشت', 'price' => 12345]);
        $sized = Product::create(['sku' => '95', 'name' => 'ادويه ترشي ۱۰۰ گ', 'price' => 12345]);
        $ambiguous = Product::create(['sku' => '96', 'name' => 'ارده', 'price' => 12345]);
        $distinct = Product::create(['sku' => '97', 'name' => 'روغن زیتون بی بو', 'price' => 12345]);

        Http::fake([
            'felfelinab.com/wp-json/wc/store/v1/products*' => Http::sequence()
                ->push([
                    ['name' => 'ادویه آبگوشت 100 گرم', 'images' => [['src' => 'https://felfelinab.com/spice.jpg']]],
                    ['name' => 'ادویه آبگوشت ۱۰۰ گرمی', 'images' => [['src' => 'https://felfelinab.com/spice.jpg']]],
                    ['name' => 'ارده 400 گرم', 'images' => [['src' => 'https://felfelinab.com/tahini-small.jpg']]],
                    ['name' => 'ارده 800 گرم', 'images' => [['src' => 'https://felfelinab.com/tahini-large.jpg']]],
                ], 200, ['X-WP-TotalPages' => '2'])
                ->push([
                    ['name' => 'ادویه ترشی (100 گرم)', 'images' => [['src' => 'https://felfelinab.com/pickle-spice.jpg']]],
                    ['name' => 'روغن زیتون با بو 675 گرم', 'images' => [['src' => 'https://felfelinab.com/olive.jpg']]],
                ]),
        ]);

        $summary = (new FelfeliPhotoSynchronizer)->sync();

        $this->assertSame('https://felfelinab.com/spice.jpg', $spice->fresh()->image);
        $this->assertSame('https://felfelinab.com/pickle-spice.jpg', $sized->fresh()->image);
        $this->assertNull($ambiguous->fresh()->image);
        $this->assertNull($distinct->fresh()->image);
        $this->assertSame(12345, $spice->fresh()->price);
        $this->assertSame(2, $summary['updated']);
        $this->assertSame(1, $summary['ambiguous']);
        Http::assertSentCount(2);
    }

    public function test_exact_felfeli_photo_updates_without_replacing_manual_upload_or_guessing_similar_names(): void
    {
        $matched = Product::create([
            'sku' => 'BARAN-201',
            'name' => 'زعفران یک گرم',
            'price' => 10000,
            'image' => 'https://felfelinab.com/old.jpg',
            'source' => 'baran',
        ]);
        $manual = Product::create([
            'sku' => 'BARAN-202',
            'name' => 'زعفران یک گرم',
            'price' => 10000,
            'image' => 'products/manual.jpg',
            'source' => 'baran',
        ]);
        $similar = Product::create([
            'sku' => 'BARAN-203',
            'name' => 'زعفران دو گرم',
            'price' => 10000,
            'source' => 'baran',
        ]);

        Http::fake([
            'felfelinab.com/wp-json/wc/store/v1/products*' => Http::response([
                ['name' => 'زعفران يك گرم', 'images' => [['src' => 'https://felfelinab.com/new.jpg']]],
                ['name' => 'زعفران دو گرم ممتاز', 'images' => [['src' => 'https://felfelinab.com/other.jpg']]],
            ], 200, ['X-WP-TotalPages' => '1']),
        ]);

        $summary = (new FelfeliPhotoSynchronizer)->sync();

        $this->assertSame('https://felfelinab.com/new.jpg', $matched->fresh()->image);
        $this->assertSame('products/manual.jpg', $manual->fresh()->image);
        $this->assertNull($similar->fresh()->image);
        $this->assertSame(1, $summary['updated']);
        $this->assertSame(1, $summary['manual_preserved']);
    }
}
