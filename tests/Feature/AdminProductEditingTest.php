<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\Integration\CatalogSynchronizer;
use App\Services\Integration\Contracts\CatalogSource;
use App\Services\Integration\DTO\CatalogItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductEditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_product_edit_page_from_its_numeric_id(): void
    {
        $product = Product::create([
            'sku' => 'BARAN-101',
            'name' => 'کالای نمونه',
            'price' => 10000,
            'source' => 'baran',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.products.edit', $product->id))
            ->assertOk();
    }

    public function test_baran_product_only_accepts_a_manual_photo_and_refreshes_other_fields_from_baran(): void
    {
        Storage::fake('public');

        $product = Product::create([
            'sku' => 'BARAN-102',
            'name' => 'نام اولیه',
            'price' => 10000,
            'stock_qty' => 4,
            'track_stock' => true,
            'source' => 'baran',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.update', $product->id), [
                '_method' => 'put',
                'sku' => $product->sku,
                'name' => 'نام ویرایش‌شده',
                'description' => 'توضیح دستی',
                'price' => 11000,
                'track_stock' => true,
                'stock_qty' => 3,
                'in_stock' => true,
                'is_active' => true,
                'image' => UploadedFile::fake()->image('manual.jpg'),
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $uploadedImage = $product->image;
        $this->assertNotNull($uploadedImage);
        Storage::disk('public')->assertExists($uploadedImage);
        $this->assertSame('نام اولیه', $product->name);
        $this->assertNull($product->description);
        $this->assertSame(10000, $product->price);
        $this->assertSame(4, $product->stock_qty);

        $source = new class implements CatalogSource
        {
            public function name(): string
            {
                return 'baran:test';
            }

            public function items(): iterable
            {
                yield new CatalogItem(
                    sku: 'BARAN-102',
                    name: 'نام باران',
                    price: 15000,
                    inStock: false,
                    stockQty: 0,
                    imageUrl: 'https://baran.example/photo.jpg',
                );
            }
        };

        (new CatalogSynchronizer($source))->sync();

        $product->refresh();
        $this->assertSame('نام باران', $product->name);
        $this->assertNull($product->description);
        $this->assertSame($uploadedImage, $product->image);
        $this->assertSame(15000, $product->price);
        $this->assertSame(0, $product->stock_qty);
        $this->assertFalse($product->in_stock);
    }

    public function test_baran_updates_an_unedited_name_but_never_supplies_the_product_photo(): void
    {
        $product = Product::create([
            'sku' => 'BARAN-103',
            'name' => 'نام قدیمی باران',
            'price' => 10000,
            'source' => 'baran',
        ]);

        $source = new class implements CatalogSource
        {
            public function name(): string
            {
                return 'baran:test';
            }

            public function items(): iterable
            {
                yield new CatalogItem(
                    sku: 'BARAN-103',
                    name: 'نام تازه باران',
                    price: 12000,
                    inStock: true,
                    stockQty: 6,
                    imageUrl: 'https://baran.example/not-our-photo.jpg',
                );
            }
        };

        (new CatalogSynchronizer($source))->sync();

        $product->refresh();
        $this->assertSame('نام تازه باران', $product->name);
        $this->assertNull($product->image);
    }

    public function test_uploaded_product_photo_is_served_without_a_public_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/manual.jpg', 'image-content');

        $product = Product::create([
            'sku' => 'BARAN-104',
            'name' => 'کالای تصویردار',
            'price' => 10000,
            'image' => 'products/manual.jpg',
            'source' => 'baran',
        ]);

        $this->get($product->image_url)
            ->assertOk()
            ->assertStreamedContent('image-content');
    }
}
