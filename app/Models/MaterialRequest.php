<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $fillable = [
        'request_number',
        'request_date',
        'due_date',
        'reason',
        'store_location',
        'status',
        'approve_area_manager',
        'approve_accounting',
        'remark_revision'
    ];

    public function storeLocation(): BelongsTo
    {
        return $this->belongsTo(StoreLocation::class, 'store_location');
    }

    public function materialRequestItems(): HasMany
    {
        return $this->hasMany(MaterialRequestItem::class, 'request_number', 'request_number');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'REQ-';
            $date = now()->format('Y');

            $latestRequest = static::where('request_number', 'like', $prefix . $date . '-' . '%')
                ->orderBy('request_number', 'desc')
                ->first();

            if ($latestRequest) {
                $lastNumber = (int) substr($latestRequest->request_number, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $model->request_number = $prefix . $date . '-' . sprintf('%04d', $nextNumber);
        });
    }
}
