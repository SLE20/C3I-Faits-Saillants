<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'commune',
        'communal_section',
        'neighborhood',
        'address',
        'latitude',
        'longitude',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}