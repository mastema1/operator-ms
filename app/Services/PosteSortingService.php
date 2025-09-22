<?php

namespace App\Services;

use App\Models\Poste;
use Illuminate\Database\Eloquent\Collection;

class PosteSortingService
{
    /**
     * The "Golden Order" - Core Named Postes in exact required order
     */
    private static array $coreNamedPostes = [
        'ABS',
        'Bol',
        'Bouchon',
        'CMC',
        'COND',
        'FILISTE',
        'FILISTE EPS',
        'FW',
        'Polyvalent',
        'Ravitailleur',
        'Retouche',
        'TAG',
        'Team Speaker',
        'VISSEUSE',
        'Goullote'
    ];

    /**
     * Apply the "Golden Order" sorting to a collection of Postes
     * 
     * Three-tiered sorting:
     * 1. Numbered Postes: "Poste 1" through "Poste 40" (numerical order)
     * 2. Core Named Postes: Specific order as defined above
     * 3. Other Postes: Alphabetical order
     */
    public static function sortPostes(Collection $postes): Collection
    {
        return $postes->sort(function ($a, $b) {
            $aCategory = self::categorizePoste($a->name);
            $bCategory = self::categorizePoste($b->name);
            
            // If different categories, sort by category priority
            if ($aCategory['category'] !== $bCategory['category']) {
                return $aCategory['category'] <=> $bCategory['category'];
            }
            
            // Same category, sort within category
            switch ($aCategory['category']) {
                case 1: // Numbered Postes
                    return $aCategory['sort_value'] <=> $bCategory['sort_value'];
                    
                case 2: // Core Named Postes
                    return $aCategory['sort_value'] <=> $bCategory['sort_value'];
                    
                case 3: // Other Postes
                    return strcasecmp($a->name, $b->name);
                    
                default:
                    return strcasecmp($a->name, $b->name);
            }
        })->values(); // Reset array keys
    }

    /**
     * Categorize a poste name and return category info
     * 
     * @param string $posteName
     * @return array ['category' => int, 'sort_value' => mixed]
     */
    private static function categorizePoste(string $posteName): array
    {
        // Category 1: Numbered Postes (Poste 01 through Poste 40, with or without zero-padding)
        if (preg_match('/^Poste (\d+)$/', $posteName, $matches)) {
            $number = (int)$matches[1];
            if ($number >= 1 && $number <= 40) {
                return [
                    'category' => 1,
                    'sort_value' => $number
                ];
            }
        }
        
        // Category 2: Core Named Postes
        $coreIndex = array_search($posteName, self::$coreNamedPostes);
        if ($coreIndex !== false) {
            return [
                'category' => 2,
                'sort_value' => $coreIndex
            ];
        }
        
        // Category 3: All Other Postes
        return [
            'category' => 3,
            'sort_value' => $posteName
        ];
    }

    /**
     * Get the core named postes list (for reference)
     */
    public static function getCoreNamedPostes(): array
    {
        return self::$coreNamedPostes;
    }

    /**
     * Check if a poste name is a numbered poste (Poste 01-40, with or without zero-padding)
     */
    public static function isNumberedPoste(string $posteName): bool
    {
        if (preg_match('/^Poste (\d+)$/', $posteName, $matches)) {
            $number = (int)$matches[1];
            return $number >= 1 && $number <= 40;
        }
        return false;
    }

    /**
     * Check if a poste name is a core named poste
     */
    public static function isCoreNamedPoste(string $posteName): bool
    {
        return in_array($posteName, self::$coreNamedPostes);
    }

    /**
     * Get the category of a poste (1=Numbered, 2=Core Named, 3=Other)
     */
    public static function getPosteCategory(string $posteName): int
    {
        return self::categorizePoste($posteName)['category'];
    }

    /**
     * Sort postes for dropdown display with proper formatting
     * Returns array suitable for HTML select options
     */
    public static function getPostesForDropdown(Collection $postes): array
    {
        $sortedPostes = self::sortPostes($postes);
        
        $options = [];
        foreach ($sortedPostes as $poste) {
            $options[$poste->id] = $poste->name;
        }
        
        return $options;
    }

    /**
     * Get postes grouped by category for advanced dropdown display
     */
    public static function getPostesGroupedByCategory(Collection $postes): array
    {
        $sortedPostes = self::sortPostes($postes);
        
        $grouped = [
            'numbered' => [],
            'core_named' => [],
            'other' => []
        ];
        
        foreach ($sortedPostes as $poste) {
            $category = self::getPosteCategory($poste->name);
            
            switch ($category) {
                case 1:
                    $grouped['numbered'][] = $poste;
                    break;
                case 2:
                    $grouped['core_named'][] = $poste;
                    break;
                case 3:
                    $grouped['other'][] = $poste;
                    break;
            }
        }
        
        return $grouped;
    }

    /**
     * Debug method to show sorting logic for a collection
     */
    public static function debugSorting(Collection $postes): array
    {
        $debug = [];
        
        foreach ($postes as $poste) {
            $category = self::categorizePoste($poste->name);
            $debug[] = [
                'id' => $poste->id,
                'name' => $poste->name,
                'category' => $category['category'],
                'sort_value' => $category['sort_value'],
                'category_name' => match($category['category']) {
                    1 => 'Numbered Poste',
                    2 => 'Core Named Poste',
                    3 => 'Other Poste',
                    default => 'Unknown'
                }
            ];
        }
        
        return $debug;
    }
}
