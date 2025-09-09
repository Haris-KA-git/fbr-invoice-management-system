<?php

namespace Database\Seeders;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoUser = User::where('email', 'demo@business.com')->first();
        $adminUser = User::where('email', 'admin@fbrvoice.com')->first();

        if ($demoUser) {
            BusinessProfile::firstOrCreate([
                'user_id' => $demoUser->id,
                'business_name' => 'Demo Trading Company',
            ], [
                'strn_ntn' => '1234567890123',
                'address' => 'Plot 123, Block A, Gulberg III, Lahore, Punjab',
                'province_code' => '01',
                'branch_name' => 'Main Branch',
                'branch_code' => 'MB001',
                'contact_phone' => '+92-42-12345678',
                'contact_email' => 'info@demotradingco.com',
                'whitelisted_ips' => ['192.168.1.100', '203.124.45.67'],
                'is_sandbox' => true,
                'is_active' => true,
            ]);

            BusinessProfile::firstOrCreate([
                'user_id' => $demoUser->id,
                'business_name' => 'Tech Solutions Pvt Ltd',
            ], [
                'strn_ntn' => '9876543210987',
                'address' => 'Office 456, IT Tower, DHA Phase 5, Karachi, Sindh',
                'province_code' => '02',
                'branch_name' => 'Karachi Office',
                'branch_code' => 'KHI001',
                'contact_phone' => '+92-21-87654321',
                'contact_email' => 'contact@techsolutions.com',
                'whitelisted_ips' => ['192.168.2.100'],
                'is_sandbox' => true,
                'is_active' => true,
            ]);
        }

        if ($adminUser) {
            BusinessProfile::firstOrCreate([
                'user_id' => $adminUser->id,
                'business_name' => 'FBR Invoice System',
            ], [
                'strn_ntn' => '1111111111111',
                'address' => 'Software House, F-7 Markaz, Islamabad',
                'province_code' => '05',
                'contact_phone' => '+92-51-11111111',
                'contact_email' => 'admin@fbrvoice.com',
                'is_sandbox' => true,
                'is_active' => true,
            ]);
        }
    }
}