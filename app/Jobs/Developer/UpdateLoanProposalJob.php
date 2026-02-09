<?php

namespace App\Jobs\Developer;

use App\Enums\LoanProposalStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Models\LoanProposal;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateLoanProposalJob
{
    protected User $user;
    protected int $loanProposalId;
    protected string $action;
    protected ?string $rejectionReason;

    public function __construct(User $user, int $loanProposalId, string $action, ?string $rejectionReason = null)
    {
        $this->user = $user;
        $this->loanProposalId = $loanProposalId;
        $this->action = $action;
        $this->rejectionReason = $rejectionReason;
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

            return match ($this->action) {
                'start_review' => $this->startReview($loanProposal),
                'accept' => $this->accept($loanProposal),
                'reject' => $this->reject($loanProposal),
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

    private function startReview(LoanProposal $loanProposal): JsonResponse
    {
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
    }

    private function accept(LoanProposal $loanProposal): JsonResponse
    {
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

            // Set lender_id and update project status to PROPOSAL_ACCEPTED
            $loanProposal->project->lender_id = $loanProposal->lender_id;
            $loanProposal->project->status = ProjectStatusEnum::PROPOSAL_ACCEPTED;
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
    }

    private function reject(LoanProposal $loanProposal): JsonResponse
    {
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

        DB::transaction(function () use ($loanProposal) {
            $loanProposal->status = LoanProposalStatusEnum::LOAN_PROPOSAL_REJECTED_BY_DEVELOPER;
            $loanProposal->rejection_reason = $this->rejectionReason;
            $loanProposal->save();

            // Check if all proposals for this project are now rejected
            $hasActiveProposals = LoanProposal::where('project_id', $loanProposal->project_id)
                ->whereNotIn('status', [
                    LoanProposalStatusEnum::LOAN_PROPOSAL_REJECTED_BY_DEVELOPER,
                    LoanProposalStatusEnum::LOAN_PROPOSAL_EXPIRED,
                ])
                ->exists();

            if (!$hasActiveProposals) {
                $loanProposal->project->status = ProjectStatusEnum::REJECTED;
                $loanProposal->project->save();
            }
        });

        $loanProposal->load(['project', 'lender.user']);

        return response()->json([
            'success' => true,
            'message' => 'Loan proposal rejected.',
            'data' => new LoanProposalResource($loanProposal),
        ]);
    }

    private function sign(LoanProposal $loanProposal): JsonResponse
    {
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

        if ($loanProposal->developer_signed_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already signed this agreement.',
            ], 403);
        }

        DB::transaction(function () use ($loanProposal) {
            $loanProposal->developer_signed_at = now();

            if ($loanProposal->lender_signed_at) {
                // Both have signed - fully executed
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_FULLY_EXECUTED;

                // Update project status to FUNDING
                $loanProposal->project->status = ProjectStatusEnum::FUNDING;
                $loanProposal->project->save();
            } else {
                $loanProposal->status = LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER;
            }

            $loanProposal->save();
        });

        $loanProposal->load(['project', 'lender.user']);

        return response()->json([
            'success' => true,
            'message' => 'Loan term agreement signed successfully.',
            'data' => new LoanProposalResource($loanProposal),
        ]);
    }
}
