<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferItemResource extends JsonResource
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
            'transferOutNumber' => $this->transfer_out_number,
            'itemName' => $this->item_name,
            'itemDescription' => $this->item_description,
            'quantity' => $this->quantity,
            'unit' => $this->unit
        ];
    }
}
