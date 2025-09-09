<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@fbrvoice.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole('Admin');

        // Create Accountant User
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@fbrvoice.com'],
            [
                'name' => 'Chief Accountant',
                'password' => Hash::make('accountant123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $accountant->assignRole('Accountant');

        // Create Cashier User
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@fbrvoice.com'],
            [
                'name' => 'Sales Cashier',
                'password' => Hash::make('cashier123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $cashier->assignRole('Cashier');

        // Create Auditor User
        $auditor = User::firstOrCreate(
            ['email' => 'auditor@fbrvoice.com'],
            [
                'name' => 'Internal Auditor',
                'password' => Hash::make('auditor123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $auditor->assignRole('Auditor');

        // Create Demo Business Owner
        $owner = User::firstOrCreate(
            ['email' => 'demo@business.com'],
            [
                'name' => 'Demo Business Owner',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $owner->assignRole('Accountant');
    }
}