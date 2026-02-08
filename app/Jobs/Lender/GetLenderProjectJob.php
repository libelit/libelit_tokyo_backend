<?php

namespace App\Jobs\Lender;

use App\Enums\KybStatusEnum;
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

            // Check KYB status
            if ($lenderProfile->kyb_status !== KybStatusEnum::APPROVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete KYB verification before viewing projects.',
                    'kyb_status' => $lenderProfile->kyb_status,
                ], 403);
            }

            $lenderId = $lenderProfile->id;

            // Query project with visibility rules:
            // - SUBMITTED: visible to all lenders (marketplace)
            // - UNDER_REVIEW/APPROVED/FUNDING/FUNDED/COMPLETED: only visible to the lender who claimed it
            $project = Project::with(['developer.user', 'lender', 'token', 'milestones', 'documents', 'photos'])
                ->withCount(['milestones', 'photos'])
                ->where('id', $this->projectId)
                ->where(function ($q) use ($lenderId) {
                    // All lenders can see SUBMITTED projects
                    $q->where('status', ProjectStatusEnum::SUBMITTED);

                    // Lender can also see projects assigned to them (exclusive visibility)
                    $q->orWhere('lender_id', $lenderId);
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
