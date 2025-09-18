<?php

namespace Database\Factories;

use App\Models\Operator;
use App\Models\Poste;
use App\Models\Tenant;
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

        // Moroccan first names
        $moroccanFirstNames = [
            'Mohammed', 'Ahmed', 'Youssef', 'Hassan', 'Omar', 'Khalid', 'Abdelaziz', 'Rachid', 'Said', 'Mustapha',
            'Abderrahim', 'Abdellah', 'Noureddine', 'Abdelkader', 'Hamid', 'Brahim', 'Larbi', 'Driss', 'Karim', 'Amine',
            'Othmane', 'Zakaria', 'Mehdi', 'Ayoub', 'Ismail', 'Soufiane', 'Hicham', 'Tarik', 'Jamal', 'Fouad',
            'Fatima', 'Aicha', 'Khadija', 'Zineb', 'Amina', 'Latifa', 'Nadia', 'Samira', 'Malika', 'Hafida',
            'Rajae', 'Souad', 'Naima', 'Karima', 'Houria', 'Saida', 'Nezha', 'Bouchra', 'Siham', 'Laila'
        ];

        // Moroccan last names
        $moroccanLastNames = [
            'Alami', 'Benali', 'Cherkaoui', 'Fassi', 'Idrissi', 'Kettani', 'Lahlou', 'Mansouri', 'Naciri', 'Ouali',
            'Benjelloun', 'Chraibi', 'Douiri', 'El Fassi', 'Ghazi', 'Hajji', 'Jaidi', 'Kabbaj', 'Lamrani', 'Mekouar',
            'El Amrani', 'Berrada', 'Tazi', 'Sefrioui', 'Benkirane', 'Chakir', 'Drissi', 'El Guerrab', 'Filali', 'Guessous',
            'Hakim', 'Jebli', 'Kadiri', 'Lazrak', 'Maârouf', 'Nejjar', 'Ouhadi', 'Qadiri', 'Riffi', 'Skalli',
            'Tahiri', 'Uazzani', 'Wahbi', 'Yacoubi', 'Zniber', 'Miloudi', 'Bigaa', 'El Gamraoui', 'Chafik', 'El Yakhlafi'
        ];

        return [
            'matricule' => strtoupper($this->faker->unique()->bothify('OP-####')), // ensure unique matricule
            'first_name' => $this->faker->randomElement($moroccanFirstNames),
            'last_name' => $this->faker->randomElement($moroccanLastNames),
            'poste_id' => Poste::inRandomOrder()->first()?->id ?? Poste::factory(),
            'is_capable' => $this->faker->boolean(80),
            'anciente' => $this->generateAnciennete(),
            'type_de_contrat' => $this->faker->randomElement($contractOptions),
            'ligne' => $this->faker->randomElement($ligneOptions),
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? Tenant::factory(),
        ];
    }

    /**
     * Generate ancienneté between 2 months and 4 years
     */
    private function generateAnciennete(): string
    {
        // Random choice between months (2-11) or years (1-4)
        $useMonths = $this->faker->boolean(30); // 30% chance for months
        
        if ($useMonths) {
            $months = $this->faker->numberBetween(2, 11);
            return $months . ' mois';
        } else {
            $years = $this->faker->numberBetween(1, 4);
            return $years . ' ans';
        }
    }
} 