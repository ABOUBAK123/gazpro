<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'store_id', 'client_id', 'order_id', 'client_name',
        'brand', 'weight', 'quantity', 'unit_price', 'amount',
        'currency', 'sale_date', 'description',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
