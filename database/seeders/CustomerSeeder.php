<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessProfiles = BusinessProfile::all();

        foreach ($businessProfiles as $profile) {
            // Registered customers
            Customer::firstOrCreate([
                'business_profile_id' => $profile->id,
                'name' => 'ABC Corporation Ltd',
            ], [
                'ntn_cnic' => '2345678901234',
                'address' => 'Industrial Area, Faisalabad, Punjab',
                'contact_phone' => '+92-41-12345678',
                'contact_email' => 'accounts@abccorp.com',
                'customer_type' => 'registered',
                'is_active' => true,
            ]);

            Customer::firstOrCreate([
                'business_profile_id' => $profile->id,
                'name' => 'XYZ Enterprises',
            ], [
                'ntn_cnic' => '3456789012345',
                'address' => 'Commercial Market, Rawalpindi, Punjab',
                'contact_phone' => '+92-51-87654321',
                'contact_email' => 'info@xyzenterprises.com',
                'customer_type' => 'registered',
                'is_active' => true,
            ]);

            // Unregistered customers
            Customer::firstOrCreate([
                'business_profile_id' => $profile->id,
                'name' => 'Ahmed Ali',
            ], [
                'ntn_cnic' => '42101-1234567-8',
                'address' => 'House 789, Street 12, Model Town, Lahore',
                'contact_phone' => '+92-300-1234567',
                'customer_type' => 'unregistered',
                'is_active' => true,
            ]);

            Customer::firstOrCreate([
                'business_profile_id' => $profile->id,
                'name' => 'Fatima Khan',
            ], [
                'ntn_cnic' => '42201-9876543-2',
                'address' => 'Flat 456, Block B, Gulshan-e-Iqbal, Karachi',
                'contact_phone' => '+92-321-9876543',
                'contact_email' => 'fatima.khan@email.com',
                'customer_type' => 'unregistered',
                'is_active' => true,
            ]);
        }
    }
}