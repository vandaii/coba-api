<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $fillable = [
        'stock_opname_number',
        'stock_opname_date',
        'input_stock_date',
        'counted_by',
        'prepared_by',
        'store_location'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'stock_opname_number', 'stock_opname_number');
    }
}
