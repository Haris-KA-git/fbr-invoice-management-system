<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UomSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            BusinessProfileSeeder::class,
            CustomerSeeder::class,
            ItemSeeder::class,
            InvoiceSeeder::class,
        ]);
    }
}