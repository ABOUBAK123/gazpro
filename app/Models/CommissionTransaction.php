<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionTransaction extends Model
{
    protected $fillable = [
        'commissionnaire_id', 'type', 'amount', 'status',
        'store_id', 'payment_id', 'phone', 'reference', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta'   => 'array',
        ];
    }

    public function commissionnaire()
    {
        return $this->belongsTo(Commissionnaire::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
