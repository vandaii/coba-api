<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'UoM',
        'no_grpo',
        'stock_opname_number',
        'request_number',
        'doc_number'
    ];

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_code', 'item_code');
    }

    public function GRPOs(): BelongsTo
    {
        return $this->belongsTo(GRPO::class, 'no_grpo', 'no_grpo');
    }

    public function stockOpnames(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_number', 'stock_opname_number');
    }

    public function materialRequests(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'request_number', 'request_number');
    }

    public function wastes(): BelongsTo
    {
        return $this->belongsTo(Waste::class, 'doc_number', 'doc_number');
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
