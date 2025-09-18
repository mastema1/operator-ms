<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'matricule',
        'first_name',
        'last_name',
        'poste_id',
        'is_capable',
        'anciente',
        'type_de_contrat',
        'ligne',
        'tenant_id',
    ];

    protected $casts = [
        'is_capable' => 'boolean',
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

    /**
     * Check if this operator's position (poste + ligne combination) is critical
     */
    public function getIsCriticalPositionAttribute(): bool
    {
        if (!$this->poste_id || !$this->ligne || !$this->tenant_id) {
            return false;
        }

        return \App\Models\CriticalPosition::isCritical($this->poste_id, $this->ligne, $this->tenant_id);
    }

    /**
     * Set the critical status for this operator's position
     */
    public function setCriticalPosition(bool $isCritical): void
    {
        if ($this->poste_id && $this->ligne && $this->tenant_id) {
            \App\Models\CriticalPosition::setCritical($this->poste_id, $this->ligne, $this->tenant_id, $isCritical);
        }
    }
} 