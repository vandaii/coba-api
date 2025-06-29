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
        'UoM',
        'no_grpo',
        'stock_opname_number'
    ];

    public function GRPOs(): BelongsTo
    {
        return $this->belongsTo(GRPO::class, 'no_grpo', 'no_grpo');
    }

    public function stockOpnames(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_number', 'stock_opname_number');
    }
}
