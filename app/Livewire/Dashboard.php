<?php

namespace App\Livewire;

use App\Models\DashboardSettings;
use App\Services\DashboardCacheManager;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    public string $search = '';
    public string $ligneFilter = '';
    
    public function updatingSearch(): void
    {
        // No pagination reset needed since we're not using pagination
    }

    public function updatingLigneFilter(): void
    {
        // No pagination reset needed since we're not using pagination
    }

    public function refreshData(): void
    {
        // Clear the dashboard cache to force fresh data
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            DashboardCacheManager::clearDashboardCache($user->tenant_id);
            
            // Also clear the lignes cache
            $lignesCacheKey = "dashboard_lignes_{$user->tenant_id}";
            \Illuminate\Support\Facades\Cache::forget($lignesCacheKey);
        }
        
        // The render method will automatically be called and fetch fresh data
    }

    public function render()
    {
        // Check if user is authenticated
        if (!auth()->check() || !auth()->user()) {
            abort(401, 'User not authenticated');
        }

        $user = auth()->user();
        if (!$user->tenant_id) {
            abort(500, 'User has no tenant assigned');
        }

        // Get dashboard data with filtering
        $dashboardData = $this->getDashboardDataWithFilters($user->tenant_id);
        
        // Get the dashboard title for this tenant
        $dashboardTitle = DashboardSettings::getTitleForTenant($user->tenant_id);
        
        // Get available lignes for the filter dropdown
        $lignes = $this->getAvailableLignes($user->tenant_id);

        return view('livewire.dashboard', array_merge($dashboardData, [
            'dashboardTitle' => $dashboardTitle,
            'lignes' => $lignes
        ]))->layout('layouts.app');
    }
    
    private function getDashboardDataWithFilters(int $tenantId): array
    {
        // Use optimized service with shorter cache duration for real-time updates
        $cacheKey = "dashboard_filtered_{$tenantId}_" . md5($this->search . $this->ligneFilter) . '_' . now()->format('Y-m-d-H-i');
        
        return Cache::remember($cacheKey, 30, function () use ($tenantId) { // 30 seconds for concurrent load optimization
            // Get optimized dashboard data using single-query approach
            $dashboardData = \App\Services\AdvancedQueryOptimizationService::getOptimizedDashboardData($tenantId);
            
            // Apply filters to the critical posts data if needed
            if (!empty($this->search) || !empty($this->ligneFilter)) {
                $filteredData = $dashboardData['criticalPostesWithOperators']->filter(function ($assignment) {
                    $matchesSearch = true;
                    $matchesLigne = true;
                    
                    // Apply search filter (name and poste) - case insensitive
                    if (!empty($this->search)) {
                        $searchTerm = strtolower(trim($this->search));
                        $operatorName = strtolower($assignment['operator_name'] ?? '');
                        $posteName = strtolower($assignment['poste_name'] ?? '');
                        
                        $matchesSearch = str_contains($operatorName, $searchTerm) || 
                                       str_contains($posteName, $searchTerm);
                    }
                    
                    // Apply ligne filter - exact match
                    if (!empty($this->ligneFilter)) {
                        $matchesLigne = ($assignment['ligne'] ?? '') === $this->ligneFilter;
                    }
                    
                    return $matchesSearch && $matchesLigne;
                });
                
                $dashboardData['criticalPostesWithOperators'] = $filteredData->values();
            }
            
            return $dashboardData;
        });
    }
    
    private function getAvailableLignes(int $tenantId): \Illuminate\Support\Collection
    {
        // Get lignes from critical positions for this tenant using optimized service
        $cacheKey = "dashboard_lignes_{$tenantId}";
        
        return Cache::remember($cacheKey, 300, function () use ($tenantId) { // 5 minutes cache
            // Use the same optimized service as the main dashboard data
            $dashboardData = \App\Services\AdvancedQueryOptimizationService::getOptimizedDashboardData($tenantId);
            
            return $dashboardData['criticalPostesWithOperators']
                ->pluck('ligne')
                ->unique()
                ->filter()
                ->sort(function ($a, $b) {
                    // Extract numeric part for natural sorting
                    $numA = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
                    $numB = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
                    return $numA <=> $numB;
                })
                ->values();
        });
    }
}
