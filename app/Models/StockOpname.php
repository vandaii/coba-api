<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $fillable = [
        'stock_opname_number',
        'stock_opname_date',
        'input_stock_date',
        'counted_by',
        'prepared_by',
        'store_location',
        'status'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'stock_opname_number', 'stock_opname_number');
    }

    public function storeLocation(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'store_location');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'SO-';
            $date = now()->format('Y');

            $latestStock = static::where('stock_opname_number', 'like', $prefix . $date . '%')
                ->orderBy('stock_opname_number', 'desc')
                ->first();

            if ($latestStock) {
                $lastNumber = (int) substr($latestStock->stock_opname_number, -3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $model->stock_opname_number = $prefix . $date . sprintf('%03d', $nextNumber);
        });
    }
}
