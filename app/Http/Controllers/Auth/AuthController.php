<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request)
    {
         $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'number'=>['required', 'string', 'max:12', 'unique:users'],
            'role'=>['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
         ]);
         $password = $request->password ?? Str::password(8);
         $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'number'=>$request->number,
            'role'=>$request->role,
            'password' => Hash::make($password),
        ]);

         $token = $user->createToken('auth-token')->plainTextToken;
         $response = [
            'message'=>'Account Created Sucessfully',
            'user'=>new UserResource($user),
            'token'=>$token
         ];
        return response($response);
        

        /*return response($request->all());*/
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8' ],
        ]);
        $column = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) !== false ? 'email' : 'number';
        $user = User::where($column, $credentials['login'])->first();

        
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response(['error'=>"Incorrect password or email", 'code'=>3], 422 );
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response([
            'message'=>'Login successful',
            'code'=>0,
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function me(Request $request)
    {
        $user = new UserResource($request->user());
        return response($user, 200);
        //return $this->success(new UserResource($request->user()));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
