<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Order\Models\Order;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_request_payment(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 200.00,
            'status' => 'pending',
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/payment/request', [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data' => ['payment_id', 'amount', 'message']]);
    }

    public function test_cannot_request_payment_for_paid_order(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 200.00,
            'status' => 'paid',
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/payment/request', [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'order_not_payable',
            ]);
    }

    public function test_cannot_request_payment_for_another_users_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $intruderToken = $intruder->createToken('test')->plainTextToken;

        $order = Order::create([
            'user_id' => $owner->id,
            'total_price' => 200.00,
            'status' => 'pending',
        ]);

        $response = $this->withToken($intruderToken)
            ->postJson('/api/v1/payment/request', [
                'order_id' => $order->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_payment(): void
    {
        $response = $this->postJson('/api/v1/payment/request', [
            'order_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_full_payment_flow(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => 150.00,
            'status' => 'pending',
        ]);

        $requestResponse = $this->withToken($token)
            ->postJson('/api/v1/payment/request', [
                'order_id' => $order->id,
            ]);

        $requestResponse->assertStatus(200);
        $paymentId = $requestResponse->json('data.payment_id');

        $verifyResponse = $this->withToken($token)
            ->postJson('/api/v1/payment/verify', [
                'payment_id' => $paymentId,
            ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonStructure(['message', 'data']);

        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_cannot_verify_another_users_payment(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'total_price' => 150.00,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($owner);
        $requestResponse = $this->postJson('/api/v1/payment/request', [
            'order_id' => $order->id,
        ]);

        $paymentId = $requestResponse->json('data.payment_id');

        Sanctum::actingAs($intruder);
        $response = $this->postJson('/api/v1/payment/verify', [
            'payment_id' => $paymentId,
        ]);

        $response->assertStatus(404);
    }
}
