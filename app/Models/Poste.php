<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_critical',
        'ligne',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
    ];

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }
} 