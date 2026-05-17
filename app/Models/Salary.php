<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'store_id', 'staff_id', 'employee_name', 'base_amount',
        'bonus', 'deductions', 'total_amount', 'currency', 'month', 'status',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'paid' ? 'Payé' : 'En attente';
    }
}
