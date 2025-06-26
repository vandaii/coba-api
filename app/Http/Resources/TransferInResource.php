<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferInResource extends JsonResource
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
            'transferInNumber' => $this->transfer_in_number,
            'transferOutNumber' => $this->transfer_out_number,
            'receiptDate' => $this->receipt_date,
            'transferDate' => $this->transfer_date,
            'sourceLocation' => new StoreLocationResource($this->sourceLocations),
            'destinationLocation' => new StoreLocationResource($this->destinationLocations),
            'delivery_note' => $this->delivery_note,
            'status' => $this->status
        ];
    }
}
