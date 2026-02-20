<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepairResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        $itemsTotal = $this->whenLoaded('repairitems', function () {
            return $this->repairitems->sum('cost');
        }, 0);

        return [
            'id'=>$this->id,
            'description'=>$this->description,
            'property'=>$this->property?->name,
            'unit'=>$this->unit?->name,
            'repair_cost'=>$this->repair_cost,
            'repairitems'=>$this->whenLoaded('repairitems'),
            'total_cost' => (float) $this->repair_cost + (float) $itemsTotal,
            'status'=>$this->status ?? 'pending',
            'created_at'=>$this->created_at
        ];
    }
}
