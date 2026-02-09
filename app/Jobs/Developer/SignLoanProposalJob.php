<?php

namespace App\Jobs\Developer;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\LoanProposal;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class SignLoanProposalJob
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

            // Check if proposal is in a state that allows signing
            $signableStatuses = [
                LoanProposalStatusEnum::LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER,
                LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER,
            ];

            if (!in_array($loanProposal->status, $signableStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This proposal is not in a state that allows signing.',
                ], 403);
            }

            // Check if developer already signed
            if ($loanProposal->developer_signed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already signed this agreement.',
                ], 403);
            }

            // Update signature timestamp
            $loanProposal->developer_signed_at = now();

            // Determine new status
            if ($loanProposal->lender_signed_at) {
                // Both have now signed
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_FULLY_EXECUTED;
            } else {
                // Only developer has signed
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER;
            }

            $loanProposal->save();
            $loanProposal->load(['project', 'lender.user']);

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
