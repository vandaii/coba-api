<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreLocation extends Model
{
    protected $fillable = [
        'store_name',
        'address'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function transferOuts(): HasMany
    {
        return $this->hasMany(TransferOut::class);
    }

    public function transferIns(): HasMany
    {
        return $this->hasMany(TransferIn::class);
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(StockOpname::class);
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(MaterialRequest::class);
    }
}
