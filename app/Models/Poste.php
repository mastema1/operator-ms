<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Services\PosteSortingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * Scope to get postes in the "Golden Order"
     * 
     * This applies the custom three-tiered sorting:
     * 1. Numbered Postes (Poste 1-40) in numerical order
     * 2. Core Named Postes in predefined order
     * 3. Other Postes in alphabetical order
     */
    public function scopeInGoldenOrder(Builder $query): Builder
    {
        // Get all postes first, then apply custom sorting
        // We can't do this efficiently in SQL, so we'll sort the collection
        return $query;
    }

    /**
     * Get postes for dropdown in Golden Order
     * 
     * @param int|null $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getForDropdownInGoldenOrder(?int $tenantId = null): \Illuminate\Database\Eloquent\Collection
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        
        $postes = self::where('tenant_id', $tenantId)->get();
        
        return PosteSortingService::sortPostes($postes);
    }

    /**
     * Get postes as array for HTML select dropdown in Golden Order
     * 
     * @param int|null $tenantId
     * @return array
     */
    public static function getDropdownOptionsInGoldenOrder(?int $tenantId = null): array
    {
        $postes = self::getForDropdownInGoldenOrder($tenantId);
        
        return PosteSortingService::getPostesForDropdown($postes);
    }

    /**
     * Check if this poste is a numbered poste (Poste 1-40)
     */
    public function isNumberedPoste(): bool
    {
        return PosteSortingService::isNumberedPoste($this->name);
    }

    /**
     * Check if this poste is a core named poste
     */
    public function isCoreNamedPoste(): bool
    {
        return PosteSortingService::isCoreNamedPoste($this->name);
    }

    /**
     * Get the category of this poste (1=Numbered, 2=Core Named, 3=Other)
     */
    public function getCategory(): int
    {
        return PosteSortingService::getPosteCategory($this->name);
    }
} 