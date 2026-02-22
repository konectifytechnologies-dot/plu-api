<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UnitResource;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Unit;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PropertyService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UnitController extends ApiController
{
    protected PropertyService $property;
    protected InvoiceService $invoice;


    public function __construct(PropertyService $property, InvoiceService $invoice)
    {
        $this->property = $property;
        $this->invoice = $invoice;
    }
    public function tenants(Request $request)
    {
        try{
            $user = $request->user();
            $isAgent =  $user->role == 'agent';
            $page = $request->input('page') ?? 1;
            $searchTerm = $request->input('query') ?? null;
            $perPage = 15;
            $properties = $isAgent ? $user->agentProperties : $user->landlordProperties;
            $ids = $properties->pluck('id');
            $items = Tenancy::with(['user'=> function ($q) {
                            $q->where('is_deleted', false); // only active tenancies
                        }, 
                            'property:id,name',
                            'unit:id,name,rent'
                        ])->whereIn('property_id', $ids)
                        ->where(['status'=>'active'])
                        ->where(function ($query) use($searchTerm){
                            if(!is_null($searchTerm)){
                                $query->where('name', 'LIKE', "%{$searchTerm}%");
                            }
                        })->paginate($perPage, ['*'], 'page', $page);
            return TenantResource::collection($items)->response();


           

         }catch(Exception $e){
            $error = $e->getMessage(); 
            return $this->error($error);
        }
    }

    public function alltenants()
    {
        try{
            $tenants = $this->invoice->generateTenantInvoices();
            return $tenants;

         }catch(Exception $e){
            $error = $e->getMessage(); 
            return $this->error($error);
        }
    }

    public function propertyTenants(string $id)
    {
        try{
            $items = Tenancy::with([
                                    'user'=> function ($q) {
                                        $q->where('is_deleted', false); // only active tenancies
                                    }, 
                                    'property:id,name',
                                    'unit:id,name,rent'
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
                            'property:id,name',
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
            $tenancyExists = Tenancy::where(['unit_id'=>$request->unit_id, 'status'=>'active'])->exists();
            if($tenancyExists){
                return $this->error('Unit is occupied');
            }
            $response = null;
            $errors = null;
            DB::transaction(function () use ($data, &$errors, &$response) {
                $password = Str::password(8);
                $property = Property::find($data['property_id']);
                $unit = Unit::find($data['unit_id']);
                $user =User::create([
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'number'=>$data['number'],
                    'role'=>'tenant',
                    'password' => Hash::make($password),
                ]);
                $response = $user;
                $tenancy = $this->property->createTenancy($user, $data['property_id'], $data['unit_id']);
                $invoice =  $this->invoice->createInvoice($property, $unit, true);
                if($tenancy['errors']){
                    $errors = $tenancy['errors'];
                }
                $response = ['tenancy'=>$tenancy, 'invoice'=>$invoice];
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
                //'tenancy_id'=>['required', 'string:exists,tenancies.id'],
                'name'=>['required', 'string'],
                'email'=>['nullable', 'string'],
                'number'=>['required', 'string'],
            ]); 
            $user = User::find($id);
                $user->update([
                    'name'=>$data['name'],
                    'email'=>$data['email'],
                    'number'=>$data['number']
                ]);
            return $this->success($user->fresh(), 'Tenant Updated');


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
