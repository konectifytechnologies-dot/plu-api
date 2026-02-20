<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
            'name'=>$this->user?->name,
            'number'=>$this->user?->number,
            'email'=>$this->user?->email,
            'property_id'=>$this->property?->id,
            'unit_id'=>$this->unit?->id,
            'user_id'=>$this->user_id,
            'house'=>$this->property?->name,
            'rent_due_date'=>$this->property?->rent_due_date ?? 5,
            'costs'=>$this->property?->costs ?? [],
            'house_number'=>$this->unit?->name,
            'rent'=>$this->unit?->rent ?? 0,
            'start_date'=>Carbon::parse($this->start_date)->format('d F Y'),
        ];
    }


}
