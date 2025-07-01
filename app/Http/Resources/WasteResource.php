<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WasteResource extends JsonResource
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
            'docNumber' => $this->doc_number,
            'wasteDate' => $this->waste_date,
            'preparedBy' => $this->prepared_by,
            'storeLocation' => new StoreLocationResource($this->whenLoaded('storeLocation')),
            'items' => ItemResource::collection($this->items),
            'wasteProof' => $this->waste_proof ? url('storage/' . $this->waste_proof) : null,
            'remark' => $this->remark,
            'approveAreaManager' => $this->approve_area_manager,
            'approveAccounting' => $this->approve_accounting,
            'status' => $this->status
        ];
    }
}
