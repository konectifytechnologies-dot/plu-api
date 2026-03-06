<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'property_id'=>$this->property_id,
            'unit_id'=>$this->unit_id,
            'invoice_number'=>$this->invoice_number,
            'status'=>$this->status,
            'invoice_data'=>$this->invoice_reminder_data ?? null,
            'invoice_url'=>$this->invoice_url,
            'due_date'=>Carbon::parse($this->due_date)->format('d F Y'),
            'created_at'=>Carbon::parse($this->created_at)->format('d F Y'),
            'items'=>$this->whenLoaded('items') ?? [],
            'total_amount' => $this->whenLoaded('items', function () {
                $total = $this->items->sum(function ($item) {
                    return ($item->total ?? 0) * ($item->quantity ?? 1);
                });
                return number_format($total); // 11,150
            }),
            'agent'=>$this->property?->agent?->first() ?? null,
            'property'=>$this->property?->name
        ];
    }
}
