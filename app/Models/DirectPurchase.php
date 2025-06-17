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
        'approve_area_manager',
        'approve_accounting',
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

        static::creating(function ($model) {
            $prefix = 'DP-1C';
            $date = now()->format('Ymd');

            $latestPurchase = static::where('no_direct_purchase', 'like', $prefix . $date . '%')
                ->orderBy('no_direct_purchase', 'desc')
                ->first();

            if ($latestPurchase) {
                $lastNumber = (int) substr($latestPurchase->no_direct_purchase, -3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $model->no_direct_purchase = $prefix . $date . sprintf('%03d', $nextNumber);
        });
    }
}
