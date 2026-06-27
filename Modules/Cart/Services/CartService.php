<?php

namespace Modules\Cart\Services;

use Modules\Cart\Exceptions\CartItemNotFoundException;
use Modules\Cart\Repositories\CartRepository;
use Modules\Order\Exceptions\InsufficientStockException;
use Modules\Product\Exceptions\InactiveProductException;
use Modules\Product\Models\Product;
use Illuminate\Support\Facades\Log;

class CartService
{
    public function __construct(
        protected CartRepository $cartRepository
    ) {}

    public function getCart($userId)
    {
        $this->cartRepository->firstOrCreateCart($userId);

        return $this->cartRepository->getCartWithItems($userId);
    }

    public function addToCart($userId, array $data)
    {
        $cart = $this->cartRepository->firstOrCreateCart($userId);
        $product = Product::query()->findOrFail($data['product_id']);

        if (!$product->is_active) {
            throw new InactiveProductException($product->title);
        }

        $existingItem = $cart->items()->where('product_id', $product->id)->first();
        $existingQuantity = $existingItem?->quantity ?? 0;
        $requestedTotal = $existingQuantity + $data['quantity'];

        if ($product->stock < $requestedTotal) {
            throw new InsufficientStockException($product->title, $product->stock, $requestedTotal);
        }

        Log::info('محصول به سبد خرید اضافه شد', [
            'user_id' => $userId,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'quantity' => $data['quantity'],
        ]);

        return $this->cartRepository->addOrUpdateItem($cart, $product->id, $data['quantity'], $product->price);
    }

    public function updateCartItem($userId, $cartItemId, array $data)
    {
        $cart = $this->cartRepository->firstOrCreateCart($userId);
        $item = $this->cartRepository->findCartItem($cart, $cartItemId);

        if (!$item) {
            throw new CartItemNotFoundException();
        }

        $product = Product::query()->findOrFail($item->product_id);

        if (!$product->is_active) {
            throw new InactiveProductException($product->title);
        }

        if ($product->stock < $data['quantity']) {
            throw new InsufficientStockException($product->title, $product->stock, $data['quantity']);
        }

        Log::info('آیتم سبد خرید بروزرسانی شد', [
            'user_id' => $userId,
            'cart_item_id' => $cartItemId,
            'new_quantity' => $data['quantity'],
        ]);

        return $this->cartRepository->updateItemQuantity($cart, $item, $data['quantity']);
    }

    public function removeCartItem($userId, $cartItemId)
    {
        $cart = $this->cartRepository->firstOrCreateCart($userId);
        $item = $this->cartRepository->findCartItem($cart, $cartItemId);

        if (!$item) {
            throw new CartItemNotFoundException();
        }

        Log::info('آیتم از سبد خرید حذف شد', [
            'user_id' => $userId,
            'cart_item_id' => $cartItemId,
        ]);

        return $this->cartRepository->removeItem($cart, $item);
    }
}
