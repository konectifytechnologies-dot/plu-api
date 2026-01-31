<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'number'=>$this->number,
            'role'=>$this->role,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'agent'=>!is_null($this->agent_id) ? $this->agent?->name : null,
            'additional_data'=>!is_null($this->additional_data) ? $this->additional_data : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
