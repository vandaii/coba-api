<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Waste extends Model
{
    protected $fillable = [
        'doc_number',
        'waste_date',
        'prepared_by',
        'approve_area_manager',
        'approve_accounting',
        'waste_proof',
        'remark',
        'status',
        'store_location'
    ];

    public function storeLocation(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'store_location');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'doc_number', 'doc_number');
    }
}
