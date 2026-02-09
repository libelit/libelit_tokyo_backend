<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\UpdateLoanProposalRequest;
use App\Jobs\Developer\GetDeveloperLoanProposalJob;
use App\Jobs\Developer\ListProjectLoanProposalsJob;
use App\Jobs\Developer\UpdateLoanProposalJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperLoanProposalController extends Controller
{
    /**
     * List all loan proposals for a project.
     */
    public function index(Request $request, int $projectId): JsonResponse
    {
        $job = new ListProjectLoanProposalsJob(
            user: $request->user(),
            projectId: $projectId,
            status: $request->get('status'),
            perPage: $request->get('per_page', 15)
        );

        return $job->handle();
    }

    /**
     * Get a single loan proposal.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = new GetDeveloperLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id
        );

        return $job->handle();
    }

    /**
     * Update loan proposal (start_review, accept, reject, sign).
     */
    public function update(UpdateLoanProposalRequest $request, int $id): JsonResponse
    {
        $job = new UpdateLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id,
            action: $request->validated('action'),
            rejectionReason: $request->validated('rejection_reason')
        );

        return $job->handle();
    }
}
