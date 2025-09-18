<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Operator;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'operator_id' => Operator::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['present', 'absent']),
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? Tenant::factory(),
        ];
    }
}
