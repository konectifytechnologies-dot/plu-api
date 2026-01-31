<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\ApiController;
use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function landlords(Request $request)
    {
        try{
        $user = $request->user();
        if($user->role !== 'agent'){
            return $this->unauthorized('Cannot add landlord: You do not have permision to do so');
        }

        $items = User::with('agent:id,email,name,number')
                            ->where('agent_id', $user->id)
                            ->get();
        $landlords = UserResource::collection($items);
        return response($landlords, 200);


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

     public function show(Request $request, string $agentId)
    {
        $agent = User::where('id', $agentId)
            ->whereIn('role', ['landlord', 'agent'])
            ->with([
                'agentProperties.units.tenancy.user', // tenants
                'agentProperties',
                'landlordProperties',
                'landlords'
            ])
            ->firstOrFail();

        $properties = $agent->role == 'agent' ? $agent->agentProperties : $agent->landlordProperties;
        $landlords = $agent->role == 'agent' ? $agent->landlords : null;

        // ---- Counts ----
        $totalProperties = $properties->count();

        $totalUnits = $properties->sum(fn ($property) =>
            $property->units->count()
        );

        $totalTenants = $properties->sum(fn ($property) =>
            $property->units->filter(fn ($unit) =>
                $unit->tenancy !== null
            )->count()
        );

        // Unique landlords across all properties
        $totalLandlords = !is_null('landlords') ? $landlords->count() : null;

        return response([
            'agent' => new UserResource($agent),
            'stats' => [
                'total_properties' => $totalProperties,
                'total_landlords'  => $totalLandlords,
                'total_units'      => $totalUnits,
                'total_tenants'    => $totalTenants,
            ],
            'properties'=>PropertyResource::collection($properties)
        ]);
    }
    


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
    public function addLandlord(Request $request, User $user)
    {
        try{
         $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'number'=>['required', 'string', 'max:12', 'unique:users'],
            'additional_data'=>['nullable', 'array'],
         ]);
        $user = $request->user();
            if($user->role !== 'agent'){
                return $this->unauthorized('Cannot add landlord: You do not have permision to do so');
        }
         $password = Str::password(8);
         $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'number'=>$request->number,
            'role'=>'landlord',
            'agent_id'=>$user->id,
            'password' => Hash::make($password),
            'additional_data'=>$request->additional_data ?? null
        ]);
        return $this->success($user, 'Landlord created successfully');
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function updateLandlord(Request $request, string $id)
    {
        try{
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'number'=>['required', 'string', 'max:12', 'unique:users']
         ]);
        $user = $request->user();
            if($user->role !== 'agent'){
                return $this->unauthorized('Cannot add landlord: You do not have permision to do so');
        }
        $landlord = User::find($id);
        if(!$landlord){
            return $this->notFound('landlord not found');
        }
        $landlord->update([
            'name'=>$request->name,
            'email'=>$request->email,
            'number'=>$request->number
        ]);
        return $this->success($landlord->fresh(), 'Landlord updated successfully');
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
