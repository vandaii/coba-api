<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'item_code',
        'item_name',
        'quantity',
        'unit'
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_purchase_order', 'no_purchase_order');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }
}
