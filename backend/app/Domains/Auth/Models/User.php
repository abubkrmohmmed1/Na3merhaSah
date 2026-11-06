<?php

namespace App\Domains\Auth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\Auth\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role',
        'email',
        'phone',
        'home_address_id',
        'home_address',
        'home_lat',
        'home_lng',
        'national_id',
        'birth_date',
        'password',
    ];

    public function address()
    {
        return $this->belongsTo(\App\Domains\Addressing\Models\Address::class, 'home_address_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'national_id',
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

    /**
     * Calculate user survey level based on points
     */
    public static function calculateLevel(int $points): array
    {
        $level = 1;
        $title = 'مستكشف مبتدئ';
        $icon = '🟢';
        $nextLevelPoints = 200;

        if ($points >= 2000) {
            $level = 5;
            $title = 'مستكشف أسطوري';
            $icon = '🔥';
            $nextLevelPoints = 5000;
        } elseif ($points >= 1000) {
            $level = 4;
            $title = 'رائد خرائط';
            $icon = '👑';
            $nextLevelPoints = 2000;
        } elseif ($points >= 500) {
            $level = 3;
            $title = 'مرشد محلي';
            $icon = '🟡';
            $nextLevelPoints = 1000;
        } elseif ($points >= 200) {
            $level = 2;
            $title = 'باحث جغرافي';
            $icon = '🔵';
            $nextLevelPoints = 500;
        }

        return compact('level', 'title', 'icon', 'nextLevelPoints');
    }

    /**
     * Get the factory for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
