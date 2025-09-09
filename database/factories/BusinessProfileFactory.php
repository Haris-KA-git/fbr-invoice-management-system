<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => $this->faker->company,
            'strn_ntn' => $this->faker->numerify('#############'),
            'address' => $this->faker->address,
            'province_code' => $this->faker->randomElement(['01', '02', '03', '04', '05']),
            'contact_phone' => $this->faker->phoneNumber,
            'contact_email' => $this->faker->companyEmail,
            'is_sandbox' => true,
            'is_active' => true,
        ];
    }
}