<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    protected $fillable = [
        'item_name',
        'quantity',
        'unit',
        'transfer_out_number'
    ];

    public function transferOuts(): BelongsTo
    {
        return $this->belongsTo(TransferOut::class, 'transfer_out_number', 'transfer_out_number');
    }
}
