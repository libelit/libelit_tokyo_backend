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
        $lendersCount = 0;

        try {
            $confirmedInvestments = $this->investments()
                ->where('status', InvestmentStatusEnum::CONFIRMED)
                ->orWhere('status', InvestmentStatusEnum::COMPLETED);

            $amountRaised = (float) $confirmedInvestments->sum('amount');
            $lendersCount = $confirmedInvestments->distinct('lender_id')->count('lender_id');
        } catch (\Exception $e) {
            // If investments relationship fails, default to 0
        }

        $loanAmount = (float) ($this->loan_amount ?? 0);

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
            'loan_amount' => $loanAmount,
            'amount_raised' => $amountRaised,
            'funding_progress' => $loanAmount > 0
                ? round(($amountRaised / $loanAmount) * 100, 2)
                : 0,
            'currency' => $this->currency ?? 'USD',
            'min_investment' => $this->min_investment,
            'status' => $this->status,
            'status_label' => $this->status?->getLabel(),
            'submitted_at' => $this->submitted_at,
            'approved_at' => $this->approved_at,
            'rejection_reason' => $this->rejection_reason,
            'listed_at' => $this->listed_at,
            'funded_at' => $this->funded_at,
            'construction_start_date' => $this->construction_start_date,
            'construction_end_date' => $this->construction_end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'developer' => new DeveloperProfileResource($this->whenLoaded('developer')),
            'lender' => new LenderProfileResource($this->whenLoaded('lender')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'documents_count' => $this->whenCounted('documents'),
            'milestones' => ProjectMilestoneResource::collection($this->whenLoaded('milestones')),
            'milestones_count' => $this->whenCounted('milestones'),
            'lenders_count' => $lendersCount,
            'cover_photo_url' => $this->getCoverPhotoUrl(),
            'photos' => $this->whenLoaded('photos', fn() => $this->photos->map(fn($photo) => [
                'id' => $photo->id,
                'uuid' => $photo->uuid,
                'title' => $photo->title,
                'file_url' => $photo->file_url,
                'file_name' => $photo->file_name,
                'file_size' => $photo->file_size,
                'mime_type' => $photo->mime_type,
                'is_featured' => $photo->is_featured,
                'sort_order' => $photo->sort_order,
            ])),
            'photos_count' => $this->whenCounted('photos'),
        ];
    }

    private function getCoverPhotoUrl(): ?string
    {
        // Use featuredPhoto relationship first
        $coverPhoto = $this->featuredPhoto;

        // Fallback to first photo if no featured photo
        if (!$coverPhoto) {
            $coverPhoto = $this->photos->first();
        }

        return $coverPhoto?->file_url;
    }
}
