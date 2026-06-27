<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Product\Models\Product;
use Tests\TestCase;

class DomainExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_returns_structured_json_error(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders/checkout');

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'error'])
            ->assertJson([
                'error' => 'empty_cart',
            ]);
    }

    public function test_inactive_product_returns_structured_json_error(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::create([
            'title' => 'Inactive',
            'slug' => 'inactive',
            'price' => 100,
            'stock' => 5,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'error'])
            ->assertJson([
                'error' => 'inactive_product',
            ]);
    }
}
