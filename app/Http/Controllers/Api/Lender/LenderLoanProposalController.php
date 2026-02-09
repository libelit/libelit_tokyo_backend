<?php

namespace App\Http\Controllers\Api\Lender;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lender\StoreLoanProposalRequest;
use App\Jobs\Lender\CreateLoanProposalJob;
use App\Jobs\Lender\GetLenderLoanProposalJob;
use App\Jobs\Lender\ListLenderLoanProposalsJob;
use App\Jobs\Lender\SignLoanProposalJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenderLoanProposalController extends Controller
{
    /**
     * List all loan proposals submitted by the lender.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new ListLenderLoanProposalsJob(
            user: $request->user(),
            status: $request->get('status'),
            perPage: $request->get('per_page', 15)
        );

        return $job->handle();
    }

    /**
     * Submit a new loan proposal for a project.
     */
    public function store(StoreLoanProposalRequest $request): JsonResponse
    {
        $job = new CreateLoanProposalJob(
            user: $request->user(),
            data: $request->validated()
        );

        return $job->handle();
    }

    /**
     * Get a single loan proposal.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = new GetLenderLoanProposalJob(
            user: $request->user(),
            loanProposalId: $id
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
            loanProposalId: $id,
            signerType: 'lender'
        );

        return $job->handle();
    }
}
