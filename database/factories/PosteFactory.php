<?php

namespace Database\Factories;

use App\Models\Poste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Poste>
 */
class PosteFactory extends Factory
{
    protected $model = Poste::class;

    public function definition(): array
    {
        $ligneOptions = [
            'Ligne 1','Ligne 2','Ligne 3','Ligne 4','Ligne 5','Ligne 6','Ligne 7','Ligne 8','Ligne 9','Ligne 10'
        ];

        $posteNames = [
            'Machine A Operator', 'Machine B Operator', 'Quality Control', 'Packaging',
            'Assembly Line Worker', 'Maintenance Technician', 'Supervisor', 'Inspector',
            'Material Handler', 'Production Coordinator', 'Safety Officer', 'Team Leader'
        ];

        return [
            'name' => $this->faker->randomElement($posteNames),
            'ligne' => $this->faker->randomElement($ligneOptions),
            'is_critical' => $this->faker->boolean(30), // 30% chance of being critical
        ];
    }
} 