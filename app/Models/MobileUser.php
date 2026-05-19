<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class MobileUser extends Authenticatable
{
    protected $table = 'mobile_users';

    protected $fillable = ['name', 'phone', 'address', 'password', 'api_token'];

    protected $hidden = ['password', 'api_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function generateToken(): string
    {
        $token = Str::random(60);
        $this->update(['api_token' => $token]);
        return $token;
    }
}
