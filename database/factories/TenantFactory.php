<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyTypes = ['Manufacturing', 'Technology', 'Healthcare', 'Automotive', 'Textile', 'Food Processing'];
        $companyNames = ['Solutions', 'Industries', 'Corp', 'Group', 'Systems', 'Technologies', 'International'];
        
        return [
            'name' => $this->faker->randomElement($companyTypes) . ' ' . $this->faker->randomElement($companyNames),
        ];
    }
}
