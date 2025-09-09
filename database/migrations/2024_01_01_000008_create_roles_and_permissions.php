<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $accountantRole = Role::create(['name' => 'Accountant']);
        $accountantRole->givePermissionTo([
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

        $cashierRole = Role::create(['name' => 'Cashier']);
        $cashierRole->givePermissionTo([
            'view business profiles',
            'view customers',
            'create customers',
            'view items',
            'view invoices',
            'create invoices',
            'download invoice pdfs',
        ]);

        $auditorRole = Role::create(['name' => 'Auditor']);
        $auditorRole->givePermissionTo([
            'view business profiles',
            'view customers',
            'view items',
            'view invoices',
            'view reports',
            'export reports',
            'view audit logs',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all permissions and roles
        Permission::query()->delete();
        Role::query()->delete();
    }
};