<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_products(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/products', [
                'title' => 'Test Product',
                'price' => 100.50,
                'stock' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('message', 'محصول با موفقیت ایجاد شد.');
    }

    public function test_customer_cannot_create_product(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer->value]);
        $token = $customer->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/products', [
                'title' => 'Test Product',
                'price' => 100,
                'stock' => 10,
            ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_product(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'title' => 'Test Product',
            'price' => 100,
            'stock' => 10,
        ]);

        $response->assertStatus(401);
    }

    public function test_product_creation_validation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/products', []);

        $response->assertStatus(422);
    }

    public function test_duplicate_product_titles_generate_unique_slugs(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/products', [
                'title' => 'Duplicate Title',
                'price' => 100,
                'stock' => 5,
            ])
            ->assertStatus(201);

        $response = $this->withToken($token)
            ->postJson('/api/v1/products', [
                'title' => 'Duplicate Title',
                'price' => 120,
                'stock' => 3,
            ]);

        $response->assertStatus(201);

        $slugs = Product::where('title', 'Duplicate Title')->pluck('slug')->all();
        $this->assertCount(2, $slugs);
        $this->assertCount(2, array_unique($slugs));
    }
}
