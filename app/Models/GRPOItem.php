<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GRPOItem extends Model
{
    protected $fillable = [
        'item_code',
        'item_name',
        'quantity',
        'unit',
        'grpo_number'
    ];

    public function grpos(): BelongsTo
    {
        return $this->belongsTo(GRPO::class, 'grpo_number', 'grpo_number');
    }

    public function items(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }
}
