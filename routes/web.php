<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/debug-csrf', function () {
    return response()->json([
        'xsrf_cookie' => request()->cookie('XSRF-TOKEN'),
        'xsrf_header' => request()->header('X-XSRF-TOKEN'),
        'session_id' => session()->getId(),
        'session' => session()->all(),
    ]);
});



