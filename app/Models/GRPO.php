<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GRPO extends Model
{
    protected $fillable = [
        'no_grpo',
        'no_po',
        'receive_date',
        'expense_type',
        'shipper_name',
        'receive_name',
        'supplier',
        'packing_slip',
        'notes'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_po', 'no_purchase_order');
    }

    public function Items(): HasMany
    {
        return $this->hasMany(Item::class, 'no_grpo', 'no_grpo');
    }
}
