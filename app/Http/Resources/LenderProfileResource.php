<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LenderProfileResource extends JsonResource
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
            'lender_type' => $this->lender_type,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'kyb_status' => $this->kyb_status,
            'kyb_submitted_at' => $this->kyb_submitted_at,
            'kyb_approved_at' => $this->kyb_approved_at,
            'kyb_rejection_reason' => $this->kyb_rejection_reason,
            'aml_status' => $this->aml_status,
            'aml_checked_at' => $this->aml_checked_at,
            'accreditation_status' => $this->accreditation_status,
            'accreditation_expires_at' => $this->accreditation_expires_at,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
