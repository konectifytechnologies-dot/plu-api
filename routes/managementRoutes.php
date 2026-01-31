<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use \App\Http\Controllers\Management\PropertyController;
use App\Http\Controllers\Management\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::post('/property', [PropertyController::class, 'create']);
    Route::patch('/property/{id}', [PropertyController::class, 'edit']);
    Route::delete('/properties', [PropertyController::class, 'destroy']);

    Route::post('/unit', [UnitController::class, 'create']);
    Route::post('/tenant', [UnitController::class, 'addTenant']);
    Route::patch('/tenant/{id}', [UnitController::class, 'updateTenant']);
    Route::delete('/vacate/tenant/{id}', [UnitController::class, 'vacateTenant']);
    Route::get('/property/tenants/{id}', [UnitController::class, 'propertyTenants']);
    Route::get('/property/units/{id}', [UnitController::class, 'propertyUnits']);
    
    
    //Route::get('me', [AuthController::class, 'me']);
});




