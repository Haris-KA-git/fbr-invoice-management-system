<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory(),
            'item_code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence,
            'hs_code' => $this->faker->numerify('####.##.##'),
            'unit_of_measure' => $this->faker->randomElement(['PCS', 'KG', 'LTR', 'MTR']),
            'tax_rate' => 17.00,
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'is_active' => true,
        ];
    }
}