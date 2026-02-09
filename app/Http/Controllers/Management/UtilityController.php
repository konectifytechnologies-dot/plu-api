<?php

namespace App\Http\Controllers\Management;

use Exception;
use Carbon\Carbon;
use App\Models\Repair;
use App\Models\Property;
use App\Models\RepairItem;
use Illuminate\Support\Str;
use App\Models\WaterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReadingResource;
use App\Http\Controllers\Api\ApiController;

class UtilityController extends ApiController 
{
  
     

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) 
    {
        try{
            $request->validate([
                'property_id'=>['required', 'string', 'exists:properties,id'],
                'unit_id'=>['required', 'string', 'exists:units,id'],
                'current_reading'=>['required', 'integer'],
                'previous_reading'=>['nullable', 'integer'],
            ]);
            $property = Property::find($request->property_id);
            if(!$property){
                return $this->error('Property not found');
            }
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;
            $lastReading = WaterReading::where('unit_id', $request->unit_id)
                            ->orderByDesc('year')
                            ->orderByDesc('month')
                            ->first();
            $previous_reading = $request->previous_reading ?? 0;
            $previousReading = $lastReading?->current_reading ??  $previous_reading;  
            $unitsConsumed = $request->current_reading - $previousReading;
             if ($unitsConsumed < 0) {
                return $this->error('Current reading cannot be less than previous reading');
            }             
           
            $totalCost = $unitsConsumed * $property->water_unit_cost;

            $add = WaterReading::create([
                'property_id' => $request->property_id,
                'unit_id' => $request->unit_id,
                'year' => $year,
                'month' => $month,
                'previous_reading' => $previousReading,
                'current_reading' => $request->current_reading,
                'units_consumed' => $unitsConsumed,
                'amount' => $totalCost,
            ]);
            return $this->success($add, 'Water reading added');
            

        }catch (Exception $e) {
            $response = ['error' => $e->getMessage(), 'code' => 3];
                Log::info($e->getMessage());
                return response($response, 500);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function index(Request $request, string $id)
    {
        try{
            $month = Carbon::parse($request->month)->month ?? Carbon::now()->month;
            $year = $request->input('year') ?? $year = Carbon::now()->year;
            $page = $request->input('page') ?? 1;
           
            $perPage =  15;
            $search = $request->input('query') ?? null;
            $readings = WaterReading::with(['property', 'unit.tenancy.user'])
                                      ->where(['property_id'=>$id, 'year'=>$year])
                                      ->where(function ($query) use ($month){
                                         if(!is_null($month)){
                                            $query->where('month', $month);
                                         }
                                       
                                      })->when($search, function ($query) use ($search) {
                                         if(!is_null($search)){
                                            $query->whereHas('unit.tenancy.user', function ($q) use ($search) {
                                                $q->where('number', $search);
                                            });
                                         }
                                      })
                                      ->paginate($perPage, ['*'], 'page', $page);
            
            return ReadingResource::collection($readings)->response();
         }catch (Exception $e) {
            $response = ['error' => $e->getMessage(), 'code' => 3];
            return response($response, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function addRepair(Request $request)
    {
        try{
            $request->validate([
                'description'=>['required', 'string'],
                'property_id'=>['required', 'string'],
                'unit_id'=>['nullable', 'string'],
                'cost'=>['nullable', 'integer'],
                'items'=>['nullable', 'array']
            ]);
            $response = [];
            DB::transaction(function () use ($request, &$response) {
                $repair = Repair::create([
                    'description'=>$request->description,
                    'property_id'=>$request->property_id,
                    'unit_id'=>$request->unit_id,
                    'repair_cost'=>$request->cost,
                    
                    
                ]);
                $items = $request->items ?? null; 
                $repairItems = [];
                if(!is_null($items)){
                    /*foreach($items as $item){
                        $repairItems[] = [
                            'id'=>Str::ulid(),
                            'name'=>$item['title'] ?? null,
                            'cost'=>$item['price'] ?? 0,
                            'repair_id'=>$repair->id

                        ];
                    }*/
                    $repairItems = array_map(
                        fn($item, $index) => [
                            'id'=>Str::ulid(),
                            'name'=>$item['title'],
                            'cost'=>$item['price'],
                            'repair_id'=>$repair->id,
                            'created_at'=>now(),
                            'updated_at'=>now()
                        
                        ],$items, array_keys($items)
                    );
                    RepairItem::insert($repairItems);
                }
                $response = ['repair'=>$repair, 'is_null'=>$repairItems];

            });
                
               
            
            return $this->success($response, 'success');
            

        }catch (Exception $e) {
            $response = ['error' => $e->getMessage(), 'code' => 3];
            return response($response, 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WaterReading $waterReading)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WaterReading $waterReading)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WaterReading $waterReading)
    {
        //
    }
}
