<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalCurrency extends Model
{
    protected $fillable = ['name', 'code', 'symbol', 'rate', 'is_default', 'active'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'active'     => 'boolean',
        ];
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
