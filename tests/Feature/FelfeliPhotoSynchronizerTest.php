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
