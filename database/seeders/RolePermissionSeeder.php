<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Business Profile permissions
            'view business profiles',
            'create business profiles',
            'edit business profiles',
            'delete business profiles',
            
            // Customer permissions
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Item permissions
            'view items',
            'create items',
            'edit items',
            'delete items',
            
            // Invoice permissions
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'submit invoices to fbr',
            'download invoice pdfs',
            
            // Report permissions
            'view reports',
            'export reports',
            
            // System permissions
            'view audit logs',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());

        $accountantRole = Role::firstOrCreate(['name' => 'Accountant']);
        $accountantRole->syncPermissions([
            'view business profiles',
            'create business profiles',
            'edit business profiles',
            'view customers',
            'create customers',
            'edit customers',
            'view items',
            'create items',
            'edit items',
            'view invoices',
            'create invoices',
            'edit invoices',
            'submit invoices to fbr',
            'download invoice pdfs',
            'view reports',
            'export reports',
        ]);

        $cashierRole = Role::firstOrCreate(['name' => 'Cashier']);
        $cashierRole->syncPermissions([
            'view business profiles',
            'view customers',
            'create customers',
            'view items',
            'view invoices',
            'create invoices',
            'download invoice pdfs',
        ]);

        $auditorRole = Role::firstOrCreate(['name' => 'Auditor']);
        $auditorRole->syncPermissions([
            'view business profiles',
            'view customers',
            'view items',
            'view invoices',
            'view reports',
            'export reports',
            'view audit logs',
        ]);
    }
}