<?php

namespace App\Jobs\Developer;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\LoanProposal;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class StartReviewLoanProposalJob
{
    protected User $user;
    protected int $loanProposalId;

    public function __construct(User $user, int $loanProposalId)
    {
        $this->user = $user;
        $this->loanProposalId = $loanProposalId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            // Find the loan proposal
            $loanProposal = LoanProposal::where('id', $this->loanProposalId)
                ->whereHas('project', function ($query) use ($developerProfile) {
                    $query->where('developer_id', $developerProfile->id);
                })
                ->first();

            if (!$loanProposal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan proposal not found.',
                ], 404);
            }

            // Check if proposal is in submitted status
            if ($loanProposal->status !== LoanProposalStatusEnum::LOAN_PROPOSAL_SUBMITTED_BY_LENDER) {
                return response()->json([
                    'success' => false,
                    'message' => 'This proposal cannot be marked for review.',
                ], 403);
            }

            $loanProposal->status = LoanProposalStatusEnum::LOAN_PROPOSAL_UNDER_REVIEW_BY_DEVELOPER;
            $loanProposal->save();

            $loanProposal->load(['project', 'lender.user']);

            return response()->json([
                'success' => true,
                'message' => 'Loan proposal is now under review.',
                'data' => new LoanProposalResource($loanProposal),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
