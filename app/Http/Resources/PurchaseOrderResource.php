<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'purchaseOrderNumber' => $this->no_purchase_order,
            'purchaseOrderDate' => $this->purchase_order_date,
            'expenseType' => $this->expense_type,
            'supplier' => $this->supplier,
            'shipperBy' => $this->shipper_by,
            'items' => PurchaseOrderItemResource::collection($this->purchaseOrderItems),
            'status' => $this->status
        ];
    }
}
