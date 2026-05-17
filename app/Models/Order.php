<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'store_id', 'client_id', 'livreur_id', 'client_name', 'client_phone', 'client_address',
        'brand', 'weight', 'quantity', 'unit_price', 'total_price', 'currency',
        'status', 'notes', 'latitude', 'longitude',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Livreur::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'yellow',
            'confirmed' => 'blue',
            'en_route'  => 'orange',
            'delivered' => 'green',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'En attente',
            'confirmed' => 'Confirmée',
            'en_route'  => 'En route',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            default     => $this->status,
        };
    }
}
