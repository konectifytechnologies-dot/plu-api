<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'date'=>$this->date,
            'description'=>$this->description,
            'payment_method'=>$this->payment_method,
            'payment_type'=>$this->payment_type,
            'cost_id'=>$this->cost_id,
            'amount_paid'=>$this->amount_paid,
            'amount_due'=>$this->amount_due,
            'property'=>$this->property?->name,
            'property_id'=>$this->property_id,
            'tenancy_id'=>$this->tenancy_id,
            'user_id'=>$this->user_id,
            'year'=>$this->year,
            'user'=>$this->user?->name,
            'reference_code'=>$this->reference_code,

        ];
    }
}
