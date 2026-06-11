<?php

namespace Modules\Cart\Services;

use Modules\Cart\Repositories\CartRepository;
use Modules\Product\Models\Product;

class CartService
{
    protected $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function getCart($userId)
    {
        $this->cartRepository->firstOrCreateCart($userId);
        return $this->cartRepository->getCartWithItems($userId);
    }

    public function addToCart($userId, array $data)
    {
        $cart = $this->cartRepository->firstOrCreateCart($userId);
        $product = Product::findOrFail($data['product_id']);

        return $this->cartRepository->addOrUpdateItem($cart, $product->id, $data['quantity'], $product->price);
    }

    public function updateCartItem($userId, $cartItemId, array $data)
    {
        $cart = $this->cartRepository->firstOrCreateCart($userId);
        return $this->cartRepository->updateItemQuantity($cart, $cartItemId, $data['quantity']);
    }

    public function removeCartItem($userId, $cartItemId)
    {
        $cart = $this->cartRepository->firstOrCreateCart($userId);
        return $this->cartRepository->removeItem($cart, $cartItemId);
    }
}