<?php

namespace Modules\Cart\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'title' => $this->product->title,
                    'slug' => $this->product->slug,
                    'price' => (float) $this->product->price,
                    'stock' => $this->product->stock,
                ];
            }),
            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'subtotal' => (float) ($this->price * $this->quantity),
        ];
    }
}
