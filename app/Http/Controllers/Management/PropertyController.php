<?php

namespace App\Http\Controllers\Management;
use Exception;
use App\Facades\Errors;
use App\Models\Property;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\PropertyService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PropertyResource;
use Illuminate\Support\Arr;

class PropertyController extends ApiController
{

   protected PropertyService $property;


    public function __construct(PropertyService $property)
    {
        $this->property = $property;
    }

    public function index(Request $request)
    {
        try{
            $user = $request->user();
            $perPage = 15;
            $perPage = min($perPage, 100); // safety cap
            $page = $request->input('page') ?? 1;
             $properties = $user->properties()
                            ->with([
                                'landlord:id,email,number,name',
                                'agent:id,email,number,name',
                                'units:id,property_id,name', // load units
                                'units.tenancy' => function ($q) {
                                    $q->where('status', 'active'); // only active tenancies
                                },
                            ])->where('is_deleted', false)
                            ->paginate($perPage, ['*'], 'page', $page);
          
         return PropertyResource::collection($properties)->response();

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
            'name'        => ['required', 'string', 'max:255'],
            'picture'     => ['nullable', 'string'],
            'units'        => ['required', 'integer'],
            'location'=>['nullable', 'string'],
            'water_cost'=>['nullable', 'integer'],
            'property_type'=>['nullable', 'string'],
            'has_service_charge'=>['nullable', 'boolean'],
            'service_charge'=>['nullable', 'integer'],
            'landlord_id' => ['nullable', 'exists:users,id'],
        ]);
       
        $user = $request->user();
        $response = [];

        DB::transaction(function () use ($data, $user, &$response) {
            $isAgent = $user->role == 'agent';
            /** -------------------------
             * Create property
             * ------------------------ */
            $property = Property::create([
                'name'    => $data['name'],
                'picture'=>$data['picture'] ?? null,
                'number_of_units'=>$data['units'] ?? 0,
                'location'=>$data['location'] ?? null,
                'water_unit_cost'=>$data['water_cost'] ?? 0,
                'property_type'=>$data['property_type'] ?? 'residential',
                'has_service_charge'=>$data['has_service_charge'] ?? false,
                'service_charge'=>$data['service_charge'] ?? 0    
            ]);

            if(!$isAgent && !empty($data['landlord_id'])){
                $this->property->attachUserToProperty(
                    $property->id,
                    $data['landlord_id'],
                    'landlord'
                );
            }

            if($isAgent){
                $this->property->attachUserToProperty(
                    $property->id,
                    $user->id,
                    'agent'
                );
                if(!empty($data['landlord_id'])){
                    $this->property->attachUserToProperty(
                        $property->id,
                        $data['landlord_id'],
                        'landlord'
                    );
                }
            }
            $response = [
                'property' => $property,
            ];
        });
        return $this->success($response, 'Property Created Successfully');
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        try{
            $data = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'picture'     => ['nullable', 'string'],
                'units'        => ['required', 'integer'],
                'location'=>['nullable', 'string'],
                'water_cost'=>['nullable', 'integer'],
                'property_type'=>['nullable', 'string'],
                'has_service_charge'=>['nullable', 'boolean'],
                'service_charge'=>['nullable', 'integer']
            ]);
            $property = Property::find($id);
            if(!$property){
                return $this->notFound('Property not found');
            }
            $property->update([
                'name'=>$data['name'],
                'picture'=>$data['picture'],
                'location'=>$data['location'],
                'water_unit_cost'=>$data['water_cost'],
                'property_type'=>$data['property_type'],
                'has_service_charge'=>$data['has_service_charge'],
                'service_charge'=>$data['service_charge'],
            ]);
            return $this->success($property->fresh(), 'Property Updated succeffully');
        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }

        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property, Request $request)
    {
        try{
            $data = $request->validate(['ids'=>['required', 'array']]);
            $ids = Arr::flatten($data['ids']);
            $property->whereIn('id', $ids)->update(['is_deleted'=>true]);
            return $this->success(null, 'Properties deleted');

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }
}
