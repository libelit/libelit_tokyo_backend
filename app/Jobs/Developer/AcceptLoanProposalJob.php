<?php

namespace App\Jobs\Developer;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\LoanProposal;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AcceptLoanProposalJob
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
            $loanProposal = LoanProposal::with('project')
                ->where('id', $this->loanProposalId)
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

            // Check if proposal is in a state that can be accepted
            $acceptableStatuses = [
                LoanProposalStatusEnum::LOAN_PROPOSAL_SUBMITTED_BY_LENDER,
                LoanProposalStatusEnum::LOAN_PROPOSAL_UNDER_REVIEW_BY_DEVELOPER,
            ];

            if (!in_array($loanProposal->status, $acceptableStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This proposal cannot be accepted in its current state.',
                ], 403);
            }

            DB::transaction(function () use ($loanProposal) {
                // Accept this proposal
                $loanProposal->status = LoanProposalStatusEnum::LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER;
                $loanProposal->accepted_at = now();
                $loanProposal->save();

                // Set lender_id on the project
                $loanProposal->project->lender_id = $loanProposal->lender_id;
                $loanProposal->project->save();

                // Auto-reject other pending proposals for the same project
                LoanProposal::where('project_id', $loanProposal->project_id)
                    ->where('id', '!=', $loanProposal->id)
                    ->whereIn('status', [
                        LoanProposalStatusEnum::LOAN_PROPOSAL_SUBMITTED_BY_LENDER,
                        LoanProposalStatusEnum::LOAN_PROPOSAL_UNDER_REVIEW_BY_DEVELOPER,
                    ])
                    ->update([
                        'status' => LoanProposalStatusEnum::LOAN_PROPOSAL_REJECTED_BY_DEVELOPER,
                        'rejection_reason' => 'Another proposal was accepted for this project.',
                    ]);
            });

            $loanProposal->load(['project', 'lender.user']);

            return response()->json([
                'success' => true,
                'message' => 'Loan proposal accepted successfully.',
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
