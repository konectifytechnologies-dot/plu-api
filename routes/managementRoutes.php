<?php

use \App\Http\Controllers\Management\PropertyController;
use App\Http\Controllers\Management\UnitController;
use App\Http\Controllers\Management\UtilityController;
use App\Http\Controllers\Management\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/sms', [PaymentController::class, 'smsid']);
Route::get('/all-tenants', [UnitController::class, 'alltenants']);

Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index']);
    Route::get('/property/{id}', [PropertyController::class, 'show']);
    Route::post('/property', [PropertyController::class, 'create']);
    Route::post('/property/{id}', [PropertyController::class, 'editProperty']);
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

    Route::post('/property/cost/{id}', [PaymentController::class, 'addCost']);
    Route::patch('/property/cost/{id}', [PaymentController::class, 'editCost']);
    Route::get('/property/costs/{id}', [PaymentController::class, 'propertyCosts']);

    Route::post('/repair', [UtilityController::class, 'addRepair']);
    Route::get('/repairs', [UtilityController::class, 'repairs']);
    Route::patch('/repair/status/{id}', [UtilityController::class, 'updateStatus']);

    Route::get('/payments', [PaymentController::class, 'payments']);
    Route::post('/payment', [PaymentController::class, 'payment']);
    Route::patch('/payment/{id}', [PaymentController::class, 'editPayment']);
    Route::delete('/payment/{id}', [PaymentController::class, 'deletePayment']);
    
    
    
    //Route::get('me', [AuthController::class, 'me']);
});




