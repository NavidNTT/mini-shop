<?php

namespace Modules\Cart\Repositories;

use Modules\Cart\Models\Cart;

class CartRepository
{
    public function firstOrCreateCart($userId)
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getCartWithItems($userId)
    {
        return Cart::with('items.product')->where('user_id', $userId)->first();
    }

    public function addOrUpdateItem(Cart $cart, $productId, $quantity, $price)
    {
        $item = $cart->items()->where('product_id', $productId)->first();
        
        if ($item) {
            $item->quantity += $quantity;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price
            ]);
        }
        
        return $cart->load('items.product');
    }

    public function updateItemQuantity(Cart $cart, $cartItemId, $quantity)
    {
        $cart->items()->where('id', $cartItemId)->update(['quantity' => $quantity]);
        return $cart->load('items.product');
    }

    public function removeItem(Cart $cart, $cartItemId)
    {
        $cart->items()->where('id', $cartItemId)->delete();
        return $cart->load('items.product');
    }
}