<?php

namespace App\Jobs\Lender;

use App\Enums\KycStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetLenderProjectJob
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
            $lenderProfile = $this->user->lenderProfile;

            // Check KYC status
            if ($lenderProfile->kyc_status !== KycStatusEnum::APPROVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete KYB verification before viewing projects.',
                    'kyb_status' => $lenderProfile->kyc_status,
                ], 403);
            }

            // Get invested project IDs
            $investedProjectIds = $lenderProfile->investments()->pluck('project_id')->toArray();

            // Public statuses lenders can view
            $publicStatuses = [
                ProjectStatusEnum::APPROVED,
                ProjectStatusEnum::FUNDING,
                ProjectStatusEnum::FUNDED,
                ProjectStatusEnum::COMPLETED,
            ];

            $project = Project::with(['developer.user', 'token', 'milestones', 'documents'])
                ->withCount('milestones')
                ->where('id', $this->projectId)
                ->where(function ($q) use ($publicStatuses, $investedProjectIds) {
                    $q->whereIn('status', $publicStatuses);

                    if (!empty($investedProjectIds)) {
                        $q->orWhereIn('id', $investedProjectIds);
                    }
                })
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
