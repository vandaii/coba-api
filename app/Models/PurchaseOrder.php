<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseOrder extends Model
{

    use HasFactory;

    protected $fillable = [
        'no_purchase_order',
        'purchase_order_date',
        'supplier',
        'status'
    ];

    public function GRPOs(): HasOne
    {
        return $this->hasOne(GRPO::class, 'no_po');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'PO-';
            $date = now()->format('Y');

            $latestPurchase = static::where('no_purchase_order', 'like', $prefix . $date . '%')
                ->orderBy('no_purchase_order', 'desc')
                ->first();

            if ($latestPurchase) {
                $lastNumber = (int) substr($latestPurchase->no_purchase_order, -3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $model->no_purchase_order = $prefix . $date . sprintf('%03d', $nextNumber);
        });
    }
}
