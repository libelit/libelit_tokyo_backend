<?php

namespace App\Http\Resources;

use App\Enums\InvestmentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Calculate amount raised from confirmed investments
        $amountRaised = 0;
        $investorsCount = 0;

        try {
            $confirmedInvestments = $this->investments()
                ->where('status', InvestmentStatusEnum::CONFIRMED)
                ->orWhere('status', InvestmentStatusEnum::COMPLETED);

            $amountRaised = (float) $confirmedInvestments->sum('amount');
            $investorsCount = $confirmedInvestments->distinct('investor_id')->count('investor_id');
        } catch (\Exception $e) {
            // If investments relationship fails, default to 0
        }

        $fundingGoal = (float) ($this->funding_goal ?? 0);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'project_type' => $this->project_type,
            'project_type_label' => $this->project_type?->getLabel(),
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'funding_goal' => $fundingGoal,
            'amount_raised' => $amountRaised,
            'funding_progress' => $fundingGoal > 0
                ? round(($amountRaised / $fundingGoal) * 100, 2)
                : 0,
            'currency' => $this->currency ?? 'USD',
            'min_investment' => $this->min_investment,
            'expected_return' => $this->expected_return,
            'loan_term_months' => $this->loan_term_months,
            'ltv_ratio' => $this->ltv_ratio,
            'risk_score' => $this->risk_score,
            'status' => $this->status,
            'status_label' => $this->status?->getLabel(),
            'submitted_at' => $this->submitted_at,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'listed_at' => $this->listed_at,
            'funded_at' => $this->funded_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'developer' => new DeveloperProfileResource($this->whenLoaded('developer')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'documents_count' => $this->whenCounted('documents'),
            'milestones_count' => $this->whenCounted('milestones'),
            'investors_count' => $investorsCount,
        ];
    }
}
