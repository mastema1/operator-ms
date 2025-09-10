<?php

namespace Database\Factories;

use App\Models\Operator;
use App\Models\Poste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Operator>
 */
class OperatorFactory extends Factory
{
    protected $model = Operator::class;

    public function definition(): array
    {
        $contractOptions = [
            'ANAPEC','AWRACH','TES','CDI','CDD 6 mois','CDD 1 ans','CDD 2 ans','CDD 3 ans'
        ];

        $ligneOptions = [
            'Ligne 1','Ligne 2','Ligne 3','Ligne 4','Ligne 5','Ligne 6','Ligne 7','Ligne 8','Ligne 9','Ligne 10'
        ];

        return [
            'matricule' => strtoupper($this->faker->unique()->bothify('OP-####')), // ensure unique matricule
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'poste_id' => Poste::factory(),
            'is_capable' => $this->faker->boolean(80),
            'anciente' => $this->faker->numberBetween(0, 30) . ' ans',
            'type_de_contrat' => $this->faker->randomElement($contractOptions),
            'ligne' => $this->faker->randomElement($ligneOptions),
            'is_critical' => $this->faker->boolean(25), // 25% chance of being critical
        ];
    }
} 