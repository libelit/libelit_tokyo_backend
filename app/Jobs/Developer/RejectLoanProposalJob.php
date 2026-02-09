<?php

namespace App\Jobs\Developer;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\LoanProposal;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class RejectLoanProposalJob
{
    protected User $user;
    protected int $loanProposalId;
    protected string $rejectionReason;

    public function __construct(User $user, int $loanProposalId, string $rejectionReason)
    {
        $this->user = $user;
        $this->loanProposalId = $loanProposalId;
        $this->rejectionReason = $rejectionReason;
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

            // Check if proposal is in a state that can be rejected
            $rejectableStatuses = [
                LoanProposalStatusEnum::LOAN_PROPOSAL_SUBMITTED_BY_LENDER,
                LoanProposalStatusEnum::LOAN_PROPOSAL_UNDER_REVIEW_BY_DEVELOPER,
            ];

            if (!in_array($loanProposal->status, $rejectableStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This proposal cannot be rejected in its current state.',
                ], 403);
            }

            $loanProposal->status = LoanProposalStatusEnum::LOAN_PROPOSAL_REJECTED_BY_DEVELOPER;
            $loanProposal->rejection_reason = $this->rejectionReason;
            $loanProposal->save();

            $loanProposal->load(['project', 'lender.user']);

            return response()->json([
                'success' => true,
                'message' => 'Loan proposal rejected.',
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
