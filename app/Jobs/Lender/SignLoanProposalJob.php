<?php

namespace App\Jobs\Lender;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class SignLoanProposalJob
{
    protected User $user;
    protected int $loanProposalId;
    protected string $signerType;

    public function __construct(User $user, int $loanProposalId, string $signerType = 'lender')
    {
        $this->user = $user;
        $this->loanProposalId = $loanProposalId;
        $this->signerType = $signerType;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            $loanProposal = $lenderProfile->loanProposals()
                ->find($this->loanProposalId);

            if (!$loanProposal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan proposal not found.',
                ], 404);
            }

            // Check if proposal is in a state that allows signing
            $signableStatuses = [
                LoanProposalStatusEnum::LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER,
                LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER,
            ];

            if (!in_array($loanProposal->status, $signableStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This proposal is not in a state that allows signing.',
                ], 403);
            }

            // Check if lender already signed
            if ($loanProposal->lender_signed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already signed this agreement.',
                ], 403);
            }

            // Update signature timestamp
            $loanProposal->lender_signed_at = now();

            // Determine new status
            if ($loanProposal->developer_signed_at) {
                // Both have now signed
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_FULLY_EXECUTED;
            } else {
                // Only lender has signed
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER;
            }

            $loanProposal->save();
            $loanProposal->load(['project', 'lender']);

            return response()->json([
                'success' => true,
                'message' => 'Loan term agreement signed successfully.',
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
