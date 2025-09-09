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
        // Admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@fbrvoice.com',
        ], [
            'name' => 'System Administrator',
            'password' => Hash::make('admin123'),
            'business_profile_limit' => 10,
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        // Accountant user
        $accountant = User::firstOrCreate([
            'email' => 'accountant@fbrvoice.com',
        ], [
            'name' => 'Senior Accountant',
            'password' => Hash::make('accountant123'),
            'business_profile_limit' => 3,
            'is_active' => true,
        ]);
        $accountant->assignRole('Accountant');

        // Cashier user
        $cashier = User::firstOrCreate([
            'email' => 'cashier@fbrvoice.com',
        ], [
            'name' => 'Cashier User',
            'password' => Hash::make('cashier123'),
            'business_profile_limit' => 1,
            'is_active' => true,
        ]);
        $cashier->assignRole('Cashier');

        // Auditor user
        $auditor = User::firstOrCreate([
            'email' => 'auditor@fbrvoice.com',
        ], [
            'name' => 'System Auditor',
            'password' => Hash::make('auditor123'),
            'business_profile_limit' => 1,
            'is_active' => true,
        ]);
        $auditor->assignRole('Auditor');

        // Demo business user
        $demo = User::firstOrCreate([
            'email' => 'demo@business.com',
        ], [
            'name' => 'Demo Business Owner',
            'password' => Hash::make('demo123'),
            'business_profile_limit' => 5,
            'is_active' => true,
        ]);
        $demo->assignRole('Accountant');
    }
}