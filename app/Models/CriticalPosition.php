<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CriticalPosition extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'poste_id',
        'ligne',
        'is_critical',
        'tenant_id',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
    ];

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class);
    }

    /**
     * Get or create a critical position record for a poste-ligne combination
     */
    public static function getOrCreate(int $posteId, string $ligne, int $tenantId): self
    {
        return self::firstOrCreate(
            [
                'poste_id' => $posteId,
                'ligne' => $ligne,
                'tenant_id' => $tenantId,
            ],
            [
                'is_critical' => false,
            ]
        );
    }

    /**
     * Check if a poste-ligne combination is critical
     * Only returns true if there's an explicit critical_positions record marked as critical
     */
    public static function isCritical(int $posteId, string $ligne, int $tenantId): bool
    {
        $position = self::where('poste_id', $posteId)
            ->where('ligne', $ligne)
            ->where('tenant_id', $tenantId)
            ->where('is_critical', true)
            ->first();

        return $position !== null;
    }

    /**
     * Set critical status for a poste-ligne combination
     */
    public static function setCritical(int $posteId, string $ligne, int $tenantId, bool $isCritical): void
    {
        $position = self::getOrCreate($posteId, $ligne, $tenantId);
        $position->update(['is_critical' => $isCritical]);
    }
}
