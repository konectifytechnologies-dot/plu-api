<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use \App\Http\Controllers\Management\PropertyController;
use App\Http\Controllers\Management\UnitController;
use App\Http\Controllers\Management\UtilityController;
use Illuminate\Support\Facades\Route;
 Route::post('/repair', [UtilityController::class, 'addRepair']);
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/property/{id}', [PropertyController::class, 'show']);
    Route::post('/property', [PropertyController::class, 'create']);
    Route::patch('/property/{id}', [PropertyController::class, 'edit']);
    Route::delete('/properties', [PropertyController::class, 'destroy']); 

    Route::get('/property/units/{id}', [UnitController::class, 'propertyUnits']);

    Route::get('/tenants', [UnitController::class, 'tenants']);
    Route::post('/unit', [UnitController::class, 'create']);
    Route::patch('/unit/{id}', [UnitController::class, 'edit']);
    Route::post('/tenant', [UnitController::class, 'addTenant']);
    Route::patch('/tenant/{id}', [UnitController::class, 'updateTenant']);
    Route::delete('/vacate/tenant/{id}', [UnitController::class, 'vacateTenant']);
    Route::get('/property/tenants/{id}', [UnitController::class, 'propertyTenants']);
    

    Route::post('/utility', [UtilityController::class, 'create']);
    Route::get('/property/readings/{id}', [UtilityController::class, 'index']);

   
    
    
    
    //Route::get('me', [AuthController::class, 'me']);
});




