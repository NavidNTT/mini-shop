<?php

namespace Modules\Order\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
                ];
            }),
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'subtotal' => (float) ($this->price * $this->quantity),
        ];
    }
}
