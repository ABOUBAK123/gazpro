<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    protected $fillable = [
        'store_id', 'points_per_unit', 'reward_threshold', 'reward_value', 'currency', 'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
