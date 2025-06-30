<?php

namespace App\Http\Resources;

use App\Models\StoreLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOpnameResource extends JsonResource
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
            'stockOpnameNumber' => $this->stock_opname_number,
            'stockOpnameDate' => $this->stock_opname_date,
            'inputStockDate' => $this->input_stock_date,
            'countedBy' => $this->counted_by,
            'preparedBy' => $this->prepared_by,
            'storeLocation' => new StoreLocationResource($this->whenLoaded('storeLocation')),
            'items' => ItemResource::collection($this->items),
            'status' => $this->status
        ];
    }
}
