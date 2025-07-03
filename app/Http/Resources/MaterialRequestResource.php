<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialRequestResource extends JsonResource
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
            'requestNumber' => $this->request_number,
            'requestDate' => $this->request_date,
            'dueDate' => $this->due_date,
            'storeLocation' => new StoreLocationResource($this->whenLoaded('storeLocation')),
            'items' => MaterialRequestItemResource::collection($this->materialRequestItems),
            'reason' => $this->reason,
            'approveAreaManager' => $this->approve_area_manager,
            'approveAccounting' => $this->approve_accounting,
            'remarkRevision' => $this->remark_revision,
            'status' => $this->status
        ];
    }
}
