<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferOutResource extends JsonResource
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
            'transferOutDate' => $this->transfer_out_date,
            'sourceLocation' => new StoreLocationResource($this->whenLoaded('sourceLocations')),
            'destinationLocation' => new StoreLocationResource($this->whenLoaded('destinationLocations')),
            'items' => TransferItemResource::collection($this->items),
            'deliveryNote' => $this->delivery_note,
            'notes' => $this->notes,
            'status' => $this->status
        ];
    }
}
