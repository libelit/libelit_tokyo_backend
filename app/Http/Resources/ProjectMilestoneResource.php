<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMilestoneResource extends JsonResource
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
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'sequence' => $this->sequence,
            'amount' => $this->amount,
            'percentage' => $this->percentage,
            'status' => $this->status,
            'status_label' => $this->status?->getLabel(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'proof_submitted_at' => $this->proof_submitted_at,
            'approved_at' => $this->approved_at,
            'approved_by' => $this->approved_by,
            'paid_at' => $this->paid_at,
            'payment_reference' => $this->payment_reference,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'proofs' => MilestoneProofResource::collection($this->whenLoaded('proofs')),
            'proofs_count' => $this->whenCounted('proofs'),
            'can_complete' => $this->canComplete(),
        ];
    }
}
