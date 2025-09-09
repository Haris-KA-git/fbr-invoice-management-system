<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
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
            $this->createDefaultBusinessProfile($admin);

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
            $this->createDefaultBusinessProfile($accountant);

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
            $this->createDefaultBusinessProfile($cashier);

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
            $this->createDefaultBusinessProfile($auditor);

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
            $this->createDefaultBusinessProfile($owner);
        });
    }

    private function createDefaultBusinessProfile(User $user)
    {
        // Check if user already has a business profile
        if ($user->businessProfiles()->count() > 0) {
            return;
        }

        $businessProfile = BusinessProfile::create([
            'user_id' => $user->id,
            'business_name' => $user->name . "'s Business",
            'address' => 'Please update your business address',
            'province_code' => '01', // Default to Punjab
            'is_sandbox' => true,
            'is_active' => true,
        ]);

        // Add user as owner of the business profile
        $businessProfile->users()->attach($user->id, [
            'role' => 'owner',
            'permissions' => json_encode([
                'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',
                'view_customers', 'create_customers', 'edit_customers',
                'view_items', 'create_items', 'edit_items',
                'view_reports'
            ]),
            'is_active' => true,
        ]);
    }
}