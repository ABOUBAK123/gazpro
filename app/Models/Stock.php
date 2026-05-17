<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stock';

    protected $fillable = ['store_id', 'brand', 'weight', 'quantity', 'unit_price', 'alert_threshold'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getStatus(): string
    {
        if ($this->quantity <= 0) return 'out';
        if ($this->quantity <= $this->alert_threshold) return 'critical';
        if ($this->quantity < 10) return 'low';
        return 'normal';
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
