<?php 

use \App\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

Route::post('/mpesa/confirm', [MpesaController::class, 'addCallBacks']);