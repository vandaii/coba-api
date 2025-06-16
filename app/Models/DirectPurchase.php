<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectPurchase extends Model
{
    protected $fillable = [
        'no_direct_purchase',
        'supplier',
        'date',
        'expense_type',
        'total_amount',
        'purchase_proof',
        'note',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(DirectPurchaseItem::class);
    }

    public function calculateTotal()
    {
        return $this->items()->sum('total_price');
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $getDirectPurchase = self::orderBy('no_direct_purchase', 'desc')->first();

            if ($getDirectPurchase) {
                $latestID = intval(substr($getDirectPurchase->no_direct_purchase, 4));
                $nextID = $latestID + 1;
            } else {
                $nextID = 1;
            }

            $model->no_direct_purchase = 'DP-' . sprintf('%04s', $nextID);
            while (self::where('no_direct_purchase', $model->no_direct_purchase)->exists()) {
                $nextID++;
                $model->no_direct_purchase = 'DP-' . sprintf('%04s', $nextID);
            }
        });
    }
}
