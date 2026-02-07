<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeveloperProfileResource extends JsonResource
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
            'user_id' => $this->user_id,
            'company_name' => $this->company_name,
            'company_registration_number' => $this->company_registration_number,
            'address' => $this->address,
            'kyb_status' => $this->kyb_status,
            'kyb_submitted_at' => $this->kyb_submitted_at,
            'kyb_approved_at' => $this->kyb_approved_at,
            'kyb_rejection_reason' => $this->kyb_rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
