<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'bedrooms'=>$this->bedrooms,
            'house'=>$this->property?->name,
            'service_charge'=>$this->property?->service_charge,
            'rent'=>$this->property?->has_service_charge ? ($this->rent * $this->property?->service_charge) : $this->rent,
            'tenant'=>!is_null($this->tenancy) ? $this->tenancy?->user : 'Un-occupied',
        ];
    }
}
