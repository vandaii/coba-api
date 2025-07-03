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
            'grpoNumber' => $this->grpo_number,
            'purchaseOrderNumber' => $this->purchase_order_number,
            'PurchaseOrderDate' => $this->purchase_order_date,
            'receiveDate' => $this->receive_date,
            'expenseType' => $this->expense_type,
            'shipperName' => $this->shipper_name,
            'receiveName' => $this->receive_name,
            'supplier' => $this->supplier,
            'items' => GRPOItemResource::collection($this->grpoItems),
            'packingSlip' => $this->packing_slip ? url('storage/' . $this->packing_slip) : null,
            'notes' => $this->notes,
            'status' => $this->status,
        ];
    }
}
