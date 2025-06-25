<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GRPOResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'noGRPO' => $this->no_grpo,
            'noPO' => $this->no_po,
            'PurchaseOrderDate' => $this->purchase_order_date,
            'receiveDate' => $this->receive_date,
            'expenseType' => $this->expense_type,
            'shipperName' => $this->shipper_name,
            'receiveName' => $this->receive_name,
            'supplier' => $this->supplier,
            'items' => ItemResource::collection($this->items),
            'packingSlip' => $this->packing_slip ? url('storage/' . $this->packing_slip) : null,
            'notes' => $this->notes,
            'status' => $this->status,
        ];
    }
}
