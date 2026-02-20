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
            'picture'=>!is_null($this->picture) ? $this->picture : 'https://disqav.s3.eu-west-1.amazonaws.com/disqav/placeholder.png',
            'units' => $this->number_of_units ?? 0,
            'occupied_units'=>$this->occupiedUnits($this->whenLoaded('units') ?? []) ?? 0,
            'vacant_units'=>$this->number_of_units - $this->occupiedUnits($this->whenLoaded('units') ?? []) ?? 0,
            'location'=>$this->location,
            'water_cost'=>$this->water_unit_cost ?? 0,
            'property_type'=>$this->property_type ?? 'residential',
            'deposit_required'=>$this->deposit_required ?? true,
            'rent_due_date'=>$this->rent_due_date ?? 5,
            'landlord_id'=>!empty($this->whenLoaded('landlord')) ? $this->landlord?->first()->id : null,
            'landlord'=>!empty($this->whenLoaded('landlord')) ? $this->getlandlord($this->landlord?->first()) : null,
            'agent'=>!empty($this->whenLoaded('agent')) ? $this->getagent($this->agent?->first()): null,
           
        ];

    }

    protected function getlandlord($landlord)
    {
        return $landlord->name;
    }

    protected function getagent($agent){
        return $agent->name;
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
