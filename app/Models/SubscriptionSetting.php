<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionSetting extends Model
{
    protected $fillable = ['monthly_price', 'yearly_price', 'currency', 'mobile_providers'];

    protected function casts(): array
    {
        return [
            'mobile_providers' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'monthly_price'   => 5000,
            'yearly_price'    => 50000,
            'currency'        => 'XOF',
            'mobile_providers' => ['Orange Money', 'Moov Money'],
        ]);
    }
}
