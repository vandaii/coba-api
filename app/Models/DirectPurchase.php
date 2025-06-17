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
        self::creating(function ($model) {
            $latestID = self::latest('created_at')->first();

            $currentYear = date('Y');
            $currentMonth = date('m');
            $currentDay = date('d');

            $directPurchaseIDPrefix = 'DP-1C' . $currentYear . $currentMonth . $currentDay;

            if ($latestID && strpos($latestID->no_direct_purchase, $directPurchaseIDPrefix)) {
                $latestIDNumber = intval(substr($latestID->no_direct_purchase, -3));
                $nextIDNumber = $latestIDNumber + 1;
            } else {
                $nextIDNumber = 1;
            }

            $model->no_direct_purchase = $directPurchaseIDPrefix . sprintf('%03s', $nextIDNumber);
        });
    }
}
