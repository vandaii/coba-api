<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectPurchaseResource extends JsonResource
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
            'noDirectPurchase' => $this->no_direct_purchase,
            'directPurchaseDate' => $this->date,
            'supplier' => $this->supplier,
            'expenseType' => $this->expense_type,
            'items' => DirectPurchaseItemResource::collection($this->items),
            'totalAmmount' => $this->total_ammount,
            'purchase_proof' => $this->purchase_proof ? url('storage/' . $this->purchase_proof) : null,
            'note' => $this->note,
            'status' => $this->status,
            'approveAreaManager' => $this->approve_area_manager,
            'approveAccounting' => $this->approve_accounting,
        ];
    }
}
