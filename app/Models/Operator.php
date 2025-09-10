<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'poste_id',
        'is_capable',
        'anciente',
        'type_de_contrat',
        'ligne',
        'is_critical',
    ];

    protected $casts = [
        'is_capable' => 'boolean',
        'is_critical' => 'boolean',
    ];

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
} 