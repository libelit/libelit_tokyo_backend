<?php

namespace App\Jobs\Lender;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\LoanProposalStatusEnum;
use App\Enums\MilestoneStatusEnum;
use App\Http\Resources\ProjectMilestoneResource;
use App\Managers\AuditTrailManager;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class RejectMilestoneJob
{
    protected User $user;
    protected int $projectId;
    protected int $milestoneId;
    protected string $rejectionReason;

    public function __construct(User $user, int $projectId, int $milestoneId, string $rejectionReason)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestoneId = $milestoneId;
        $this->rejectionReason = $rejectionReason;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            // Check if lender has access to this project
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

            $milestone = $project->milestones()->find($this->milestoneId);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found.',
                ], 404);
            }

            // Check if milestone can be rejected
            if ($milestone->status !== MilestoneStatusEnum::PROOF_SUBMITTED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only milestones with submitted proofs can be rejected. Current status: ' . $milestone->status->getLabel(),
                ], 422);
            }

            // Reject the milestone
            $milestone->update([
                'status' => MilestoneStatusEnum::REJECTED,
                'rejection_reason' => $this->rejectionReason,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            $milestone->refresh();
            $milestone->load('proofs');
            $milestone->loadCount('proofs');

            // Record blockchain audit trail for milestone rejection
            AuditTrailManager::record(
                BlockchainAuditEventTypeEnum::MILESTONE_REJECTED,
                $milestone,
                ['rejection_reason' => $this->rejectionReason]
            );

            return response()->json([
                'success' => true,
                'message' => 'Milestone rejected. Developer can resubmit proofs.',
                'data' => new ProjectMilestoneResource($milestone),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
