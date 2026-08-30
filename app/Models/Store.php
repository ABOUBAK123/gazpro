<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Store extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'store_name', 'owner_name', 'avatar', 'email', 'phone', 'password',
        'address', 'latitude', 'longitude', 'status',
        'subscription_status', 'subscription_expiry',
        'plan_id', 'qr_token', 'qr_code_path', 'qr_generated_at', 'commissionnaire_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'subscription_expiry' => 'datetime',
            'qr_generated_at' => 'datetime',
            // Without an explicit cast, MySQL decimal columns come back from
            // PDO as strings — fine for Blade views, but breaks the Flutter
            // app's `latitude as num?` JSON parsing (it expects a real
            // number, not "5.3599510").
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function commissionnaire()
    {
        return $this->belongsTo(Commissionnaire::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function stock()
    {
        return $this->hasMany(Stock::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function loyaltyProgram()
    {
        return $this->hasOne(LoyaltyProgram::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active'
            && ($this->subscription_expiry === null || $this->subscription_expiry->isFuture());
    }

    public function getRole(): string
    {
        return 'manager';
    }
}
