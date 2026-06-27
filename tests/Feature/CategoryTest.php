<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_categories(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_customer_cannot_create_category(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer->value]);
        $token = $customer->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/categories', [
                'name' => 'Test Category',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $category = \Modules\Category\Models\Category::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $response = $this->withToken($token)
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $category->fresh()->name);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $category = \Modules\Category\Models\Category::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
        ]);

        $response = $this->withToken($token)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'دسته‌بندی با موفقیت حذف شد.',
            ]);
    }

    public function test_cannot_set_category_as_its_own_parent(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $category = \Modules\Category\Models\Category::create([
            'name' => 'Self Parent',
            'slug' => 'self-parent',
        ]);

        $response = $this->withToken($token)
            ->putJson("/api/v1/categories/{$category->id}", [
                'parent_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'invalid_category_hierarchy',
            ]);
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $token = $admin->createToken('test')->plainTextToken;

        $category = \Modules\Category\Models\Category::create([
            'name' => 'With Products',
            'slug' => 'with-products',
        ]);

        \Modules\Product\Models\Product::create([
            'title' => 'Linked Product',
            'slug' => 'linked-product',
            'price' => 100,
            'stock' => 5,
            'category_id' => $category->id,
        ]);

        $response = $this->withToken($token)
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'category_has_products',
            ]);
    }
}
