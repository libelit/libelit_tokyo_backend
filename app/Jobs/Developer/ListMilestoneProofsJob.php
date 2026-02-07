<?php

namespace App\Jobs\Developer;

use App\Http\Resources\MilestoneProofResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListMilestoneProofsJob
{
    protected User $user;
    protected int $projectId;
    protected int $milestoneId;

    public function __construct(User $user, int $projectId, int $milestoneId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestoneId = $milestoneId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            $milestone = $project->milestones()->find($this->milestoneId);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found.',
                ], 404);
            }

            $proofs = $milestone->proofs()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'proofs' => MilestoneProofResource::collection($proofs),
                    'count' => $proofs->count(),
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
