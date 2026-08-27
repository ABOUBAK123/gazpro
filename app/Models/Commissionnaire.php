<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Commissionnaire extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password', 'code', 'status', 'balance'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'balance'  => 'decimal:2',
        ];
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function transactions()
    {
        return $this->hasMany(CommissionTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRole(): string
    {
        return 'commissionnaire';
    }
}
