<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReadingResource extends JsonResource
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
            'unit_id'=>$this->unit_id,
            'property_id'=>$this->property_id,
            'previous_reading'=>$this->previous_reading ?? 0,
            'current_reading'=>$this->current_reading,
            'amount'=>$this->amount,
            'units_consumed'=>$this->units_consumed,
            'month'=>$this->month,
            'year'=>$this->year,
            'date'=>Carbon::create()->month($this->month)->format('F').'-'.$this->year,
            'property'=>$this->property?->name,
            'house'=>$this->unit?->name,
            'tenant'=>$this->unit?->tenancy?->user?->name
        ];
    }
}
