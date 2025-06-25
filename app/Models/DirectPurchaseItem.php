<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectPurchaseItem extends Model
{
    protected $fillable = [
        'direct_purchase_id',
        'item_name',
        'item_description',
        'quantity',
        'price',
        'total_price',
        'unit'
    ];

    public function directPurchase()
    {
        return $this->belongsTo(DirectPurchase::class);
    }
}
