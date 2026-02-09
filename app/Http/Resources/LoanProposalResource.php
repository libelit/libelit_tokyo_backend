<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'loan_amount_offered' => (float) $this->loan_amount_offered,
            'currency' => $this->currency,
            'interest_rate' => (float) $this->interest_rate,
            'loan_maturity_date' => $this->loan_maturity_date,
            'security_packages' => $this->security_packages,
            'max_ltv_accepted' => $this->max_ltv_accepted ? (float) $this->max_ltv_accepted : null,
            'bid_expiry_date' => $this->bid_expiry_date,
            'additional_conditions' => $this->additional_conditions,
            'status' => $this->status,
            'status_label' => $this->status?->getLabel(),
            'rejection_reason' => $this->rejection_reason,
            'accepted_at' => $this->accepted_at,
            'developer_signed_at' => $this->developer_signed_at,
            'lender_signed_at' => $this->lender_signed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Conditional relationships
            'project' => new ProjectResource($this->whenLoaded('project')),
            'lender' => new LenderProfileResource($this->whenLoaded('lender')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'documents_count' => $this->whenCounted('documents'),
        ];
    }
}
