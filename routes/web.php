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
});

require __DIR__.'/auth.php';