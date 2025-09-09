<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory(),
            'name' => $this->faker->name,
            'ntn_cnic' => $this->faker->numerify('#############'),
            'address' => $this->faker->address,
            'contact_phone' => $this->faker->phoneNumber,
            'contact_email' => $this->faker->email,
            'customer_type' => $this->faker->randomElement(['registered', 'unregistered']),
            'is_active' => true,
        ];
    }
}