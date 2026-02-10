<?php

namespace App\Jobs\Lender;

use App\Enums\LoanProposalStatusEnum;
use App\Http\Resources\ProjectMilestoneResource;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetLenderMilestoneJob
{
    protected User $user;
    protected int $projectId;
    protected int $milestoneId;

    public function __construct(User $user, int $projectId, int $milestoneId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestoneId = $milestoneId;
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

            $milestone = $project->milestones()
                ->with('proofs')
                ->withCount('proofs')
                ->find($this->milestoneId);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
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
