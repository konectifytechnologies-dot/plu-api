<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name'=>$this->name,
            'picture'=>$this->picture,
            'units' => $this->number_of_units,
            'occupied_units'=>$this->occupiedUnits($this->whenLoaded('units') ?? []),
            'vacant_units'=>$this->number_of_units - $this->occupiedUnits($this->whenLoaded('units') ?? []),
            'location'=>$this->location,
            'water_cost'=>$this->water_unit_cost ?? 0,
            'property_type'=>$this->property_type ?? 'residential',
            'has_service_charge'=>$this->has_service_charge ?? false,
            'service_charge'=>$this->service_charge ?? 0,
            'owned_by'=>!empty($this->whenLoaded('landlord')) ? $this->landlord?->first() : null,
            'managed_by'=>!empty($this->whenLoaded('agent')) ? $this->agent?->first() : null,
           
        ];

    }

    protected  function occupiedUnits($units):int
    {
        if(empty($units)){
            return 0;
        }
       $occupied_units = $units->filter(function ($unit) {
            return !is_null($unit?->tenancy) && $unit?->tenancy->count() > 0;
       })->count() ?? 0;

       return $occupied_units;

    }
}
