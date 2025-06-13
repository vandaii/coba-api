<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'confirmed_password',
        'phone',
        'store_location',
        'photo_profile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'confirmed_password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $getUser = self::orderBy('employee_id', 'desc')->first();

            if ($getUser) {
                $latestID = intval(substr($getUser->employee_id, 4));
                $nextID = $latestID + 1;
            } else {
                $nextID = 1;
            }

            $model->employee_id = 'HS-' . sprintf('%04s', $nextID);
            while (self::where('employee_id', $model->employee_id)->exists()) {
                $nextID++;
                $model->employee_id = 'HS-' . sprintf('%04s', $nextID);
            }
        });
    }
}
