<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Customer Import/Export APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/customers/import', [App\Http\Controllers\Api\CustomerApiController::class, 'import']);
    Route::get('/customers/export', [App\Http\Controllers\Api\CustomerApiController::class, 'export']);
    Route::post('/items/import', [App\Http\Controllers\Api\ItemApiController::class, 'import']);
    Route::get('/items/export', [App\Http\Controllers\Api\ItemApiController::class, 'export']);
});