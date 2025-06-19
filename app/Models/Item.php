<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'item_code',
        'item_name',
        'quantity',
        'unit',
        'no_grpo'
    ];

    public function GRPOs(): BelongsTo
    {
        return $this->belongsTo(GRPO::class, 'no_grpo', 'no_grpo');
    }
}
