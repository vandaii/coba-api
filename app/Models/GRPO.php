<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GRPO extends Model
{
    protected $fillable = [
        'no_grpo',
        'po_id',
        'receive_date',
        'expense_type',
        'shipper_name',
        'receive_name',
        'supplier',
        'item_code',
        'item_name',
        'item_quantity',
        'item_unit',
        'packing_slip',
        'notes'
    ];

    public function PurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
