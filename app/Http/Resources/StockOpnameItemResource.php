<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOpnameItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'itemCode' => $this->item_code,
            'itemName' => $this->item_name,
            'quantity' => $this->quantity,
            'UoM' => $this->UoM,
            'stockOpnameNumber' => $this->stock_opname_number
        ];
    }
}
