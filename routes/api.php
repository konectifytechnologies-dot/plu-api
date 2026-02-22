<?php
include('managementRoutes.php');
include('mpesaRoutes.php');
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
   
});

Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/agent', [AuthController::class, 'show']);
    Route::patch('/user/{id}', [AuthController::class, 'editUser']);
    Route::post('/landlord', [AuthController::class, 'addLandlord']);
    Route::get('/landlords', [AuthController::class, 'landlords']);
    
});




