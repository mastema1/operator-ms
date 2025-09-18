<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'ligne',
        'tenant_id',
    ];

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    public function backupAssignments(): HasMany
    {
        return $this->hasMany(BackupAssignment::class);
    }
} 