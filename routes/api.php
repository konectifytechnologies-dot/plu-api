<?php
include('managementRoutes.php');
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

});

Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('/landlord', [AuthController::class, 'addLandlord']);
    Route::get('/landlords', [AuthController::class, 'landlords']);
    Route::get('/agent/{id}', [AuthController::class, 'show']);
});




