<?php

namespace App\Jobs\Lender;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\ProjectMilestoneResource;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListLenderMilestonesJob
{
    protected User $user;
    protected int $projectId;

    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            // Check if lender has access to this project
            // Either assigned as lender_id OR has an accepted loan proposal
            $project = Project::where('id', $this->projectId)
                ->where(function ($query) use ($lenderProfile) {
                    $query->where('lender_id', $lenderProfile->id)
                        ->orWhereHas('loanProposals', function ($q) use ($lenderProfile) {
                            $q->where('lender_id', $lenderProfile->id)
                                ->whereIn('status', [
                                    LoanProposalStatusEnum::LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER,
                                    LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER,
                                    LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER,
                                    LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_FULLY_EXECUTED,
                                ]);
                        });
                })
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or you do not have access to this project.',
                ], 404);
            }

            $milestones = $project->milestones()
                ->with('proofs')
                ->withCount('proofs')
                ->orderBy('sequence')
                ->get();

            // Calculate statistics
            $totalMilestones = $milestones->count();
            $completedMilestones = $milestones->whereIn('status', ['approved', 'paid'])->count();
            $pendingReview = $milestones->where('status', 'proof_submitted')->count();
            $approvedCount = $milestones->where('status', 'approved')->count();
            $paidCount = $milestones->where('status', 'paid')->count();
            $totalAmount = $milestones->sum('amount');
            $paidAmount = $milestones->where('status', 'paid')->sum('amount');
            $approvedAmount = $milestones->where('status', 'approved')->sum('amount');
            $pendingAmount = $milestones->where('status', 'proof_submitted')->sum('amount');

            return response()->json([
                'success' => true,
                'data' => ProjectMilestoneResource::collection($milestones),
                'statistics' => [
                    'total_milestones' => $totalMilestones,
                    'completed_milestones' => $completedMilestones,
                    'pending_review' => $pendingReview,
                    'approved' => $approvedCount,
                    'paid' => $paidCount,
                    'progress_percentage' => $totalMilestones > 0
                        ? round(($completedMilestones / $totalMilestones) * 100, 2)
                        : 0,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'approved_amount' => $approvedAmount,
                    'pending_amount' => $pendingAmount,
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
