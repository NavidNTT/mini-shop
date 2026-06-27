<?php

namespace Modules\Cart\Repositories;

use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;

class CartRepository
{
    public function firstOrCreateCart($userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getCartWithItems($userId): ?Cart
    {
        return Cart::with('items.product')->where('user_id', $userId)->first();
    }

    public function findCartItem(Cart $cart, $cartItemId): ?CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('id', $cartItemId)
            ->first();
    }

    public function addOrUpdateItem(Cart $cart, $productId, $quantity, $price): Cart
    {
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->price = $price;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        return $cart->load('items.product');
    }

    public function updateItemQuantity(Cart $cart, CartItem $item, $quantity): Cart
    {
        $item->update(['quantity' => $quantity]);

        return $cart->load('items.product');
    }

    public function removeItem(Cart $cart, CartItem $item): Cart
    {
        $item->delete();

        return $cart->load('items.product');
    }
}
