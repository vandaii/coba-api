<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Psy\CodeCleaner\AssignThisVariablePass;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'UoM',
    ];

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_code', 'item_code');
    }

    public function grpoItems(): HasMany
    {
        return $this->hasMany(GRPOItem::class, 'item_code', 'item_code');
    }

    public function materialRequestItems(): HasMany
    {
        return $this->hasMany(MaterialRequestItem::class, 'item_code', 'item_code');
    }

    public function stockOpnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class, 'item_code', 'item_code');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'V-';

            $latestItem = static::where('item_code', 'like', $prefix . '%')
                ->orderBy('item_code', 'desc')
                ->first();

            if ($latestItem) {
                $lastNumber = (int) substr($latestItem->item_code, -3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $model->item_code = $prefix . sprintf('%03d', $nextNumber);
        });
    }
}
