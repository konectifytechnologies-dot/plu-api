<?php

namespace App\Http\Controllers\Management;

use Exception;
use App\Models\Unit;
use App\Models\User;
use App\Models\Tenancy;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\PropertyService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UnitResource;

class UnitController extends ApiController
{
    protected PropertyService $property;


    public function __construct(PropertyService $property)
    {
        $this->property = $property;
    }
    public function index()
    {
        //
    }

    public function propertyTenants(string $id)
    {
        try{
            $items = Tenancy::with([
                                    'user'=> function ($q) {
                                        $q->where('is_deleted', false); // only active tenancies
                                    }, 
                                    'property:id,name',
                                    'unit:id,name'
                                ])->where(['property_id'=>$id, 'status'=>'active'])
                                ->get();
            $tenants = TenantResource::collection($items);
            return response($tenants, 200);
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function propertyUnits(string $id)
    {
        try{
            $items = Unit::with([
                            'property:id,name,has_service_charge,service_charge',
                            'tenancy.user:id,name,number'
                            ])->where('property_id', $id)->get();
            $units = UnitResource::collection($items);
            return response($units, 200);

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try{
            $data = $request->validate([
                'name'=>['required', 'string'],
                'bedrooms'=>['required', 'integer'],
                'rent'=>['required', 'integer'],
                'property_id'=>['required', 'exists:properties,id']
            ]);
            $add = Unit::create([
                'name'=>$data['name'],
                'bedrooms'=>(int) $data['bedrooms'],
                'rent'=>(int) $data['rent'],
                'property_id'=>$data['property_id']
            ]);
            return $this->success($add, 'Unit added successfully');
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }
 
    /**
     * Store a newly created resource in storage.
     */
    public function addTenant(Request $request)
    {
        try{
            $data = $request->validate([
                'name'=>['required', 'string'],
                'email'=>['nullable', 'string'],
                'number'=>['required', 'string'],
                'unit_id'=>['required', 'string'],
                'property_id'=>['required', 'string'],
            ]); 
            $tenancyExists = Tenancy::where(['unit_id'=>$data['unit_id'], 'status'=>'active'])->exists();
            if($tenancyExists){
                return $this->error('Unit is occupied');
            }
            $response = null;
            $errors = null;
            DB::transaction(function () use ($data, &$errors, &$response) {
                $password = $request->password ?? Str::password(8);

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'number'=>$data['number'],
                    'role'=>'tenant',
                    'password' => Hash::make($password),
                ]);
                $tenancy = $this->property->createTenancy($user, $data['property_id'], $data['unit_id']);
                if($tenancy['errors']){
                    $errors = $tenancy['errors'];
                }
                $response = $tenancy;
            });
            if(!is_null($errors)){
                return $this->error($errors['error']);
            }
            return $this->success($response, 'Tenant Created Successfully');

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit, Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'name'=>['required', 'string'],
                'bedrooms'=>['required', 'integer'],
                'rent'=>['required', 'integer'],
            ]);
            $unit = Unit::find($id);
            if(!$unit){
                return $this->error('Unit not found');
            }
            $unit->update($data);
            return $this->success($unit->fresh(), 'Unit updated successfully');
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateTenant(Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'tenancy_id'=>['required', 'string:exists,tenancies.id'],
                'name'=>['required', 'string'],
                'email'=>['nullable', 'string'],
                'number'=>['required', 'string'],
                'unit_id'=>['required', 'string'],
                'property_id'=>['required', 'string'],
            ]); 
            $tenancyExists = Tenancy::where(['unit_id'=>$data['unit_id'], 'status'=>'active'])->exists();
            $user = User::with('tenancy')->where('id', $id)->firstOrFail();
            $tenancy = $user->tenancy->first();
            $isSameUnitId = $data['unit_id'] == $tenancy->id;
            if($isSameUnitId && $tenancyExists){
                return $this->error('This Unit is already occupied');
            }
            DB::transaction(function () use ($data,$id, &$errors, $user, &$response) {
                $user->update([
                    'name'=>$data['name'],
                    'email'=>$data['email'],
                    'number'=>$data['number']
                ]);
                $tenancy = $this->property->updateTenancy($data['tenancy_id'], $data['property_id'], $data['unit_id']);
                $response = $tenancy;
            });
            return $this->success(['id'=>$isSameUnitId], 'Tenant Updated');


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function vacateTenant(Request $request, string $id)
    {
        try{
            $user = $request->user();
            if($user->role == 'tenant'){
                return $this->unauthorized('Cannot vacate tenant');
            }

            $tenant = User::find($id);
            if(!$tenant){
                return $this->notFound('Tenant not found');
            }
            $response = null;
            DB::transaction(function () use ($id, $tenant, &$response) {
                $tenant->update(['is_deleted'=>true]);
                $tenancy = Tenancy::where('user_id', $id)->delete();
            });

            return $this->success(null, 'Tenant Vacated');


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }
}
