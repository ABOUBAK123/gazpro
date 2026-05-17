<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Store extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'store_name', 'owner_name', 'email', 'phone', 'password',
        'address', 'latitude', 'longitude', 'status',
        'subscription_status', 'subscription_expiry',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'subscription_expiry' => 'datetime',
        ];
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
