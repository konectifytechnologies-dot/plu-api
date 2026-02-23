<?php

namespace App\Http\Controllers\Management;
use App\Facades\Errors;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Queries\AppQuery;
use App\Services\PropertyService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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
            $searchTerm = $request->query('query') ?? null;
             $properties = $user->properties()
                            ->with([
                                'landlord:id,email,number,name',
                                'agent:id,email,number,name',
                                'units:id,property_id,name', // load units
                                'units.tenancy' => function ($q) {
                                    $q->where('status', 'active'); // only active tenancies
                                },
                            ])->where('is_deleted', false)
                             ->where(function ($query) use($searchTerm){
                                    if(!is_null($searchTerm)){
                                        $query->where('name', 'LIKE', "%{$searchTerm}%");
                                    }
                            })->paginate($perPage, ['*'], 'page', $page);
          
         return PropertyResource::collection($properties)->response();

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function show(string $id):Response
    {
        try{

        $property = AppQuery::propertyQueries()->where('id', $id)->firstOrFail();

        return response(new PropertyResource($property));

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
            $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'picture'     => ['nullable', 'image'],
            'units'        => ['required', 'integer'],
            'location'=>['nullable', 'string'],
            'water_cost'=>['nullable', 'integer'],
            'property_type'=>['nullable', 'string'],
            'deposit_required'=>['nullable', 'string'],
            'rent_due_date'=>['nullable', 'integer'],
            'landlord_id' => ['nullable', 'exists:users,id'],
        ]);

       
        $user = $request->user();
        $response = [];

        DB::transaction(function () use ($user, $request, &$response) {
            $isAgent = $user->role == 'agent';
            $imageUrl = null;
            
            if ($request->hasFile('picture')) {
                try{
                    $file = $request->file('picture');
                    $extension = $file->getClientOriginalExtension();
                    $name = $file->getClientOriginalName();
                    $filename = Str::random(8) . '.' . $extension;
                    $folderPath = 'plu/';

                    $path =  Storage::disk('s3')->putFileAs($folderPath, $file, $filename);
                    $imageUrl = 'plu/'.$filename;
                }catch(Exception $e){
                    $error = $e->getMessage();
                    return $this->error($error);
                }
                
            }
            
            $property = Property::create([
                'name'    => $request->name,
                'picture'=>$imageUrl ?? null,
                'number_of_units'=>intVal($request->units) ?? 0,
                'location'=>$request->location ?? null,
                'water_unit_cost'=>intval($request->water_cost) ?? null,
                'property_type'=>$request->property_type ?? 'residential',
                'deposit_required'=>filter_var($request->deposit_required, FILTER_VALIDATE_BOOL) ?? true,
                'rent_due_date'=>intVal($request->rent_due_date ) ?? 5
            ]);
           
            if(!$isAgent && !empty($request->landlord_id)){
                $this->property->attachUserToProperty(
                    $property->id,
                    $request->landlord_id,
                    'landlord'
                );
            }

            if($isAgent){
                $this->property->attachUserToProperty(
                    $property->id,
                    $user->id,
                    'agent'
                );
                if(!empty($request->landlord_id)){
                    $this->property->attachUserToProperty(
                        $property->id,
                        $request->landlord_id,
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
    public function single(Request $request, string $id)
    {
        try{
            $property = AppQuery::propertyQueries()->where('id', $id)->firstOrFail();
            $item = new PropertyResource($property);
            return response($item, 200);

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    

    /**
     * Show the form for editing the specified resource.
     */
    public function editProperty(Request $request, string $id)
    {
        try{
            $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'picture'     => ['nullable', 'image', 'max:2048'],
                'units'        => ['required', 'integer'],
                'location'=>['nullable', 'string'],
                'water_cost'=>['nullable', 'integer'],
                'property_type'=>['nullable', 'string'],
                'deposit_required'=>['nullable', 'string'],
                'rent_due_date'=>['nullable', 'integer']
            ]);
            $property = Property::find($id);
            if(!$property){
                return $this->notFound('Property not found');
            }
            $imageUrl = $property->picture;
            if ($request->hasFile('picture')) {
                try{
                    $file = $request->file('picture');
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::random(8) . '.' . $extension;
                    $folderPath = 'plu/';

                    $path =  Storage::disk('s3')->putFileAs($folderPath, $file, $filename);
                    $imageUrl = 'plu/'.$filename;
                }catch(Exception $e){
                    $error = $e->getMessage();
                    return $this->error($error);
                }
                
            }
            $property->update([
                'name'    => $request->name,
                'picture'=>$imageUrl ?? null,
                'number_of_units'=>intVal($request->units) ?? 0,
                'location'=>$request->location ?? null,
                'water_unit_cost'=>intval($request->water_cost) ?? null,
                'property_type'=>$request->property_type ?? 'residential',
                'deposit_required'=>filter_var($request->deposit_required, FILTER_VALIDATE_BOOL) ?? true,
                'rent_due_date'=>intVal($request->rent_due_date ) ?? 5
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
