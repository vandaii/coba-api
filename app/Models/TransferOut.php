<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransferOut extends Model
{
    protected $fillable = [
        'transfer_out_number',
        'transfer_out_date',
        'source_location_id',
        'destination_location_id',
        'delivery_note',
        'notes',
        'status'
    ];

    public function sourceLocations(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'source_location_id');
    }

    public function destinationLocations(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'destination_location_id');
    }

    public function transferIns(): HasOne
    {
        return $this->hasOne(TransferIn::class, 'transfer_out_number');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class, 'transfer_out_number', 'transfer_out_number');
    }
}
