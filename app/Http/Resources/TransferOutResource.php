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
            'transferOutNumber' => $this->transfer_out_number,
            'transferOutDate' => $this->transfer_out_date,
            'sourceLocation' => StoreLocationResource::where('store_name', $this->storeLocation)
        ];
    }
}
