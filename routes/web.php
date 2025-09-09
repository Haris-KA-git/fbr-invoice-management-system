<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Business Profiles
    Route::resource('business-profiles', BusinessProfileController::class)->middleware('permission:view business profiles');

    // Customers
    Route::resource('customers', CustomerController::class)->middleware('permission:view customers');

    // Items
    Route::resource('items', ItemController::class)->middleware('permission:view items');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->middleware('permission:view invoices');
    Route::get('invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.download-pdf')
        ->middleware('permission:download invoice pdfs');
    Route::post('invoices/{invoice}/submit-to-fbr', [InvoiceController::class, 'submitToFbr'])
        ->name('invoices.submit-to-fbr')
        ->middleware('permission:submit invoices to fbr');

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:view reports')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [App\Http\Controllers\ReportController::class, 'salesReport'])->name('sales');
        Route::get('/customers', [App\Http\Controllers\ReportController::class, 'customerReport'])->name('customers');
        Route::get('/items', [App\Http\Controllers\ReportController::class, 'itemReport'])->name('items');
        Route::get('/tax', [App\Http\Controllers\ReportController::class, 'taxReport'])->name('tax');
        Route::get('/export/sales', [App\Http\Controllers\ReportController::class, 'exportSales'])->name('export.sales')->middleware('permission:export reports');
    });

    // User Management
    Route::prefix('users')->name('users.')->middleware('permission:manage users')->group(function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('store');
        Route::get('/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
        
        // Role Management
        Route::get('/roles/index', [App\Http\Controllers\UserController::class, 'roles'])->name('roles')->middleware('permission:manage roles');
        Route::get('/roles/create', [App\Http\Controllers\UserController::class, 'createRole'])->name('create-role')->middleware('permission:manage roles');
        Route::post('/roles', [App\Http\Controllers\UserController::class, 'storeRole'])->name('store-role')->middleware('permission:manage roles');
        Route::get('/roles/{role}/edit', [App\Http\Controllers\UserController::class, 'editRole'])->name('edit-role')->middleware('permission:manage roles');
        Route::put('/roles/{role}', [App\Http\Controllers\UserController::class, 'updateRole'])->name('update-role')->middleware('permission:manage roles');
        Route::delete('/roles/{role}', [App\Http\Controllers\UserController::class, 'destroyRole'])->name('destroy-role')->middleware('permission:manage roles');
    });
});

require __DIR__.'/auth.php';