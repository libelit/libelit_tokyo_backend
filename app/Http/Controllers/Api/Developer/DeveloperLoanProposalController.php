<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\RejectLoanProposalRequest;
use App\Jobs\Developer\AcceptLoanProposalJob;
use App\Jobs\Developer\GetDeveloperLoanProposalJob;
use App\Jobs\Developer\ListProjectLoanProposalsJob;
use App\Jobs\Developer\RejectLoanProposalJob;
use App\Jobs\Developer\SignLoanProposalJob;
use App\Jobs\Developer\StartReviewLoanProposalJob;
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
     * Mark a loan proposal as under review.
     */
    public function startReview(Request $request, int $id): JsonResponse
    {
        $job = new StartReviewLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id
        );

        return $job->handle();
    }

    /**
     * Accept a loan proposal.
     */
    public function accept(Request $request, int $id): JsonResponse
    {
        $job = new AcceptLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id
        );

        return $job->handle();
    }

    /**
     * Reject a loan proposal.
     */
    public function reject(RejectLoanProposalRequest $request, int $id): JsonResponse
    {
        $job = new RejectLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id,
            rejectionReason: $request->validated('rejection_reason')
        );

        return $job->handle();
    }

    /**
     * Sign the loan term agreement.
     */
    public function sign(Request $request, int $id): JsonResponse
    {
        $job = new SignLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id
        );

        return $job->handle();
    }
}
