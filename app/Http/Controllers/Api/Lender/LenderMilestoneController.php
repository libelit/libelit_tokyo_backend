<?php

namespace App\Http\Controllers\Api\Lender;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lender\RejectMilestoneRequest;
use App\Http\Requests\Lender\UploadPaymentProofRequest;
use App\Jobs\Lender\ApproveMilestoneJob;
use App\Jobs\Lender\GetLenderMilestoneJob;
use App\Jobs\Lender\ListLenderMilestonesJob;
use App\Jobs\Lender\RejectMilestoneJob;
use App\Jobs\Lender\UploadPaymentProofJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenderMilestoneController extends Controller
{
    /**
     * List all milestones for a project.
     */
    public function index(Request $request, int $projectId): JsonResponse
    {
        $job = new ListLenderMilestonesJob(
            user: $request->user(),
            projectId: $projectId
        );

        return $job->handle();
    }

    /**
     * Get a single milestone with all proofs.
     */
    public function show(Request $request, int $projectId, int $milestoneId): JsonResponse
    {
        $job = new GetLenderMilestoneJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId
        );

        return $job->handle();
    }

    /**
     * Approve a milestone invoice.
     */
    public function approve(Request $request, int $projectId, int $milestoneId): JsonResponse
    {
        $job = new ApproveMilestoneJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId
        );

        return $job->handle();
    }

    /**
     * Reject a milestone invoice with reason.
     */
    public function reject(RejectMilestoneRequest $request, int $projectId, int $milestoneId): JsonResponse
    {
        $job = new RejectMilestoneJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId,
            rejectionReason: $request->validated('rejection_reason')
        );

        return $job->handle();
    }

    /**
     * Upload payment proof(s) for an approved milestone.
     */
    public function uploadPaymentProof(UploadPaymentProofRequest $request, int $projectId, int $milestoneId): JsonResponse
    {
        $job = new UploadPaymentProofJob(
            user: $request->user(),
            projectId: $projectId,
            milestoneId: $milestoneId,
            proofs: $request->validated('proofs'),
            paymentReference: $request->validated('payment_reference')
        );

        return $job->handle();
    }
}
