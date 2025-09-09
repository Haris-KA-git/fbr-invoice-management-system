<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Business Profiles
    Route::middleware('permission:view business profiles')->group(function () {
        Route::resource('business-profiles', BusinessProfileController::class);
        Route::get('business-profiles/{businessProfile}/users', [BusinessProfileController::class, 'users'])->name('business-profiles.users');
        Route::post('business-profiles/{businessProfile}/users', [BusinessProfileController::class, 'addUser'])->name('business-profiles.add-user');
        Route::put('business-profiles/{businessProfile}/users/{user}', [BusinessProfileController::class, 'updateUser'])->name('business-profiles.update-user');
        Route::delete('business-profiles/{businessProfile}/users/{user}', [BusinessProfileController::class, 'removeUser'])->name('business-profiles.remove-user');
    });

    // Customers
    Route::middleware('permission:view customers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    // Items
    Route::middleware('permission:view items')->group(function () {
        Route::resource('items', ItemController::class);
    });

    // Invoices
    Route::middleware('permission:view invoices')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');
        Route::post('invoices/{invoice}/submit-fbr', [InvoiceController::class, 'submitToFbr'])->name('invoices.submit-fbr');
        Route::get('invoices/{invoice}/discard', [InvoiceController::class, 'discard'])->name('invoices.discard');
        Route::post('invoices/{invoice}/discard', [InvoiceController::class, 'storeDiscard'])->name('invoices.store-discard');
        Route::post('invoices/{invoice}/restore', [InvoiceController::class, 'restore'])->name('invoices.restore');
        Route::post('invoices/{invoice}/activate', [InvoiceController::class, 'activate'])->name('invoices.activate');
    });

    // Reports
    Route::middleware('permission:view reports')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
        Route::get('reports/customers', [ReportController::class, 'customerReport'])->name('reports.customers');
        Route::get('reports/items', [ReportController::class, 'itemReport'])->name('reports.items');
        Route::get('reports/tax', [ReportController::class, 'taxReport'])->name('reports.tax');
        Route::get('reports/export/sales', [ReportController::class, 'exportSales'])->name('reports.export.sales');
    });

    // User Management (Admin only)
    Route::middleware('permission:manage users')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('users/roles/index', [UserController::class, 'roles'])->name('users.roles');
        Route::get('users/roles/create', [UserController::class, 'createRole'])->name('users.create-role');
        Route::post('users/roles', [UserController::class, 'storeRole'])->name('users.store-role');
        Route::get('users/roles/{role}/edit', [UserController::class, 'editRole'])->name('users.edit-role');
        Route::put('users/roles/{role}', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::delete('users/roles/{role}', [UserController::class, 'destroyRole'])->name('users.destroy-role');
    });

    // Audit Logs
    Route::middleware('permission:view audit logs')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

require __DIR__.'/auth.php';