<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory(),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'invoice_number' => 'INV-' . date('Y') . '-' . $this->faker->unique()->numberBetween(100000, 999999),
            'invoice_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'invoice_type' => 'sales',
            'subtotal' => $this->faker->randomFloat(2, 1000, 50000),
            'sales_tax' => $this->faker->randomFloat(2, 170, 8500),
            'total_amount' => $this->faker->randomFloat(2, 1170, 58500),
            'fbr_status' => $this->faker->randomElement(['pending', 'validated', 'submitted', 'failed']),
        ];
    }
}