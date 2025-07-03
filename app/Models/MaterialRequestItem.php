<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestItem extends Model
{
    protected $fillable = [
        'item_code',
        'item_name',
        'quantity',
        'unit',
        'request_number'
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'request_number', 'request_number');
    }

    public function items(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }
}
