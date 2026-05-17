<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $table = 'staff';

    protected $fillable = ['store_id', 'name', 'email', 'phone', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
