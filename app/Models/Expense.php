<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['store_id', 'type', 'description', 'amount', 'currency', 'expense_date'];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'electricity' => 'Électricité',
            'water'       => 'Eau',
            'rent'        => 'Loyer',
            'maintenance' => 'Maintenance',
            'salary'      => 'Salaire',
            'other'       => 'Autre',
            default       => $this->type,
        };
    }
}
