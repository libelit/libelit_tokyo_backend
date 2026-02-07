<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\CompleteMilestoneRequest;
use App\Http\Requests\Developer\StoreProjectMilestonesRequest;
use App\Jobs\Developer\CompleteMilestoneJob;
use App\Jobs\Developer\DeleteMilestoneProofJob;
use App\Jobs\Developer\ListMilestoneProofsJob;
use App\Jobs\Developer\ListProjectMilestonesJob;
use App\Jobs\Developer\StoreProjectMilestonesJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    /**
     * List all milestones for a project.
     */
    public function index(Request $request, int $projectId): JsonResponse
    {
        $job = new ListProjectMilestonesJob(
            user: $request->user(),
            projectId: $projectId
        );

        return $job->handle();
    }

    /**
     * Store milestones for a project (bulk create/replace).
     */
    public function store(StoreProjectMilestonesRequest $request, int $projectId): JsonResponse
    {
        $job = new StoreProjectMilestonesJob(
            user: $request->user(),
            projectId: $projectId,
            milestones: $request->validated('milestones')
        );

        return $job->handle();
    }

    /**
     * Complete a milestone with proofs.
     */
    public function complete(CompleteMilestoneRequest $request, int $projectId, int $milestoneId): JsonResponse
    {
        $job = new CompleteMilestoneJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId,
            proofs: $request->validated('proofs')
        );

        return $job->handle();
    }

    /**
     * List proofs for a milestone.
     */
    public function listProofs(Request $request, int $projectId, int $milestoneId): JsonResponse
    {
        $job = new ListMilestoneProofsJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId
        );

        return $job->handle();
    }

    /**
     * Delete a proof from a milestone.
     */
    public function deleteProof(Request $request, int $projectId, int $milestoneId, int $proofId): JsonResponse
    {
        $job = new DeleteMilestoneProofJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId,
            proofId: $proofId
        );

        return $job->handle();
    }
}
