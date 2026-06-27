<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_checkout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $product = Product::create([
            'title' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/orders/checkout', [
                'notes' => 'لطفاً سریع ارسال شود.',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('data.notes', 'لطفاً سریع ارسال شود.');
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/orders/checkout');

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'empty_cart',
            ]);
    }

    public function test_cannot_checkout_with_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $product = Product::create([
            'title' => 'Low Stock Product',
            'slug' => 'low-stock',
            'price' => 50.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        $addResponse = $this->withToken($token)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $itemId = $addResponse->json('data.items.0.id');

        $response = $this->withToken($token)
            ->putJson("/api/v1/cart/update/{$itemId}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'insufficient_stock',
            ]);
    }

    public function test_authenticated_user_can_view_orders(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_guest_cannot_checkout(): void
    {
        $response = $this->postJson('/api/v1/orders/checkout');

        $response->assertStatus(401);
    }
}
