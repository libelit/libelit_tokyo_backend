<?php

namespace App\Jobs\Developer;

use App\Enums\DocumentTypeEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\ProjectResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class SubmitProjectJob
{
    protected User $user;
    protected int $projectId;

    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()
                ->with('documents')
                ->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            // Only allow submitting draft projects
            if ($project->status !== ProjectStatusEnum::DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft projects can be submitted for review.',
                ], 403);
            }

            // Check if all required documents are uploaded
            $requiredTypes = [
                DocumentTypeEnum::LOAN_DRAWINGS->value,
                DocumentTypeEnum::LOAN_COST_CALCULATION->value,
                DocumentTypeEnum::LOAN_PHOTOS->value,
                DocumentTypeEnum::LOAN_LAND_TITLE->value,
                DocumentTypeEnum::LOAN_BANK_STATEMENT->value,
            ];

            $uploadedTypes = $project->documents
                ->pluck('document_type')
                ->map(fn ($type) => $type->value)
                ->toArray();

            $missingTypes = array_diff($requiredTypes, $uploadedTypes);

            if (!empty($missingTypes)) {
                $missingDocuments = array_map(function ($type) {
                    $enum = DocumentTypeEnum::from($type);
                    return [
                        'type' => $type,
                        'label' => $enum->getLabel(),
                    ];
                }, $missingTypes);

                return response()->json([
                    'success' => false,
                    'message' => 'Please upload all required documents before submitting.',
                    'missing_documents' => array_values($missingDocuments),
                ], 422);
            }

            // Check if milestones are defined and total equals funding goal
            $milestones = $project->milestones()->get();

            if ($milestones->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please define project milestones before submitting. Go to the Milestones tab to add milestones.',
                    'missing_milestones' => true,
                ], 422);
            }

            $totalMilestoneAmount = $milestones->sum('amount');
            $fundingGoal = (float) $project->funding_goal;

            if (abs($totalMilestoneAmount - $fundingGoal) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total milestone amounts must equal the project funding goal.',
                    'milestone_total' => $totalMilestoneAmount,
                    'funding_goal' => $fundingGoal,
                ], 422);
            }

            // Update project status
            $project->update([
                'status' => ProjectStatusEnum::SUBMITTED,
                'submitted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Project submitted for review successfully. Our team will review your project.',
                'data' => new ProjectResource($project->fresh()),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
