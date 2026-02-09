<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
        public function __invoke()
    {
        request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'number'=>['required', 'string', 'max:12', 'unique:users'],
            'role'=>['nullable', 'string'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'number'=>request('number'),
            'role'=>request('role') ?? 'agent',
            'password' => Hash::make(request('password')),
        ]);
        //return response($user)

        Auth::guard('web')->login($user);
    }

}
