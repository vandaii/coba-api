<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferIn extends Model
{
    protected $fillable = [
        'transfer_in_number',
        'transfer_out_number',
        'receipt_date',
        'transfer_date',
        'source_location_id',
        'destination_location_id',
        'receive_name',
        'delivery_note',
        'notes',
        'status'
    ];

    public function transferOuts(): BelongsTo
    {
        return $this->belongsTo(TransferOut::class, 'transfer_out_number', 'transfer_out_number');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class, 'transfer_in_number', 'transfer_in_number');
    }
}
