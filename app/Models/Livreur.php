<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Livreur extends Model
{
    protected $fillable = [
        'name', 'phone', 'vehicle_type', 'vehicle_plate', 'status', 'access_token',
        'latitude', 'longitude', 'is_available',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->access_token = Str::random(48);
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getVehicleIconAttribute(): string
    {
        return match($this->vehicle_type) {
            'moto'     => 'fa-motorcycle',
            'tricycle' => 'fa-truck',
            'voiture'  => 'fa-car',
            default    => 'fa-bicycle',
        };
    }

    public function getVehicleLabelAttribute(): string
    {
        return match($this->vehicle_type) {
            'moto'     => 'Moto',
            'tricycle' => 'Tricycle',
            'voiture'  => 'Voiture',
            default    => $this->vehicle_type,
        };
    }

    public function getActiveOrdersCountAttribute(): int
    {
        return $this->orders()->whereIn('status', ['confirmed', 'en_route'])->count();
    }
}
