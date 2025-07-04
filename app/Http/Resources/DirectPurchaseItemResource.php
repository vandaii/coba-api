<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectPurchaseItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'itemName' => $this->item_name,
            'itemDescription' => $this->item_description,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'unit' => $this->unit,
            'totalPrice' => $this->total_price,
        ];
    }
}
