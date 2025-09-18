<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupAssignment extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'poste_id',
        'backup_operator_id',
        'backup_slot',
        'assigned_date',
        'tenant_id'
    ];

    protected $casts = [
        'assigned_date' => 'date'
    ];

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class);
    }

    public function backupOperator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'backup_operator_id');
    }
}
