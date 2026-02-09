<?php

namespace App\Jobs\Lender;

use App\Enums\LoanProposalStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateLoanProposalJob
{
    protected User $user;
    protected int $loanProposalId;
    protected string $action;

    public function __construct(User $user, int $loanProposalId, string $action)
    {
        $this->user = $user;
        $this->loanProposalId = $loanProposalId;
        $this->action = $action;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            $loanProposal = $lenderProfile->loanProposals()
                ->with(['project', 'lender'])
                ->find($this->loanProposalId);

            if (!$loanProposal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan proposal not found.',
                ], 404);
            }

            return match ($this->action) {
                'sign' => $this->sign($loanProposal),
                default => response()->json([
                    'success' => false,
                    'message' => 'Invalid action.',
                ], 400),
            };
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function sign($loanProposal): JsonResponse
    {
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

        if ($loanProposal->lender_signed_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already signed this agreement.',
            ], 403);
        }

        DB::transaction(function () use ($loanProposal) {
            $loanProposal->lender_signed_at = now();

            if ($loanProposal->developer_signed_at) {
                // Both have signed - fully executed
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_FULLY_EXECUTED;

                // Update project status to FUNDING
                $loanProposal->project->status = ProjectStatusEnum::FUNDING;
                $loanProposal->project->save();
            } else {
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER;
            }

            $loanProposal->save();
        });

        $loanProposal->load(['project', 'lender']);

        return response()->json([
            'success' => true,
            'message' => 'Loan term agreement signed successfully.',
            'data' => new LoanProposalResource($loanProposal),
        ]);
    }
}
