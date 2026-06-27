<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Cart\Models\CartItem;
use Modules\Product\Models\Product;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_guest_cannot_view_cart(): void
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(401);
    }

    public function test_user_can_add_product_to_cart(): void
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

        $response = $this->withToken($token)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_cannot_add_nonexistent_product(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/cart/add', [
                'product_id' => 999,
                'quantity' => 1,
            ]);

        $response->assertStatus(422);
    }

    public function test_cannot_add_inactive_product_to_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $product = Product::create([
            'title' => 'Inactive Product',
            'slug' => 'inactive-product',
            'price' => 100.00,
            'stock' => 10,
            'is_active' => false,
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'inactive_product',
            ]);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $product = Product::create([
            'title' => 'Low Stock Product',
            'slug' => 'low-stock',
            'price' => 50.00,
            'stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/cart/add', [
                'product_id' => $product->id,
                'quantity' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'insufficient_stock',
            ]);
    }

    public function test_user_cannot_update_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $product = Product::create([
            'title' => 'Shared Product',
            'slug' => 'shared-product',
            'price' => 100.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $itemId = CartItem::query()
            ->where('product_id', $product->id)
            ->value('id');

        Sanctum::actingAs($intruder);
        $response = $this->putJson("/api/v1/cart/update/{$itemId}", [
            'quantity' => 3,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'cart_item_not_found',
            ]);
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $product = Product::create([
            'title' => 'Remove Product',
            'slug' => 'remove-product',
            'price' => 100.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $itemId = CartItem::query()
            ->where('product_id', $product->id)
            ->value('id');

        Sanctum::actingAs($intruder);
        $response = $this->deleteJson("/api/v1/cart/item/{$itemId}");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'cart_item_not_found',
            ]);
    }
}
