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
        'purchase_order_number',
        'purchase_order_date',
        'expense_type',
        'supplier',
        'shipper_by',
        'status'
    ];

    public function GRPOs(): HasOne
    {
        return $this->hasOne(GRPO::class, 'no_po');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_number', 'purchase_order_number');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'PO-';
            $date = now()->format('Y');

            $latestPurchase = static::where('purchase_order_number', 'like', $prefix . $date . '%')
                ->orderBy('purchase_order_number', 'desc')
                ->first();

            if ($latestPurchase) {
                $lastNumber = (int) substr($latestPurchase->purchase_order_number, -3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $model->purchase_order_number = $prefix . $date . sprintf('%03d', $nextNumber);
        });
    }
}
