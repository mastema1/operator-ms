<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardSettings extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the tenant that owns the dashboard settings.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create dashboard settings for a tenant
     */
    public static function getForTenant(?int $tenantId = null): self
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                throw new \Exception('User not authenticated');
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                throw new \Exception('User has no tenant assigned');
            }
        }
        
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'title' => 'Postes critiques EGR ICE1',
                'settings' => []
            ]
        );
    }

    /**
     * Update the dashboard title for a tenant
     */
    public static function updateTitle(string $title, ?int $tenantId = null): self
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                throw new \Exception('User not authenticated');
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                throw new \Exception('User has no tenant assigned');
            }
        }
        
        $settings = self::getForTenant($tenantId);
        $settings->update(['title' => $title]);
        
        return $settings;
    }

    /**
     * Get the dashboard title for a tenant
     */
    public static function getTitleForTenant(?int $tenantId = null): string
    {
        if ($tenantId === null) {
            if (!auth()->check() || !auth()->user()) {
                return 'Postes critiques EGR ICE1'; // Return default title if not authenticated
            }
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return 'Postes critiques EGR ICE1'; // Return default title if no tenant
            }
        }
        
        $settings = self::where('tenant_id', $tenantId)->first();
        
        return $settings ? $settings->title : 'Postes critiques EGR ICE1';
    }
}
