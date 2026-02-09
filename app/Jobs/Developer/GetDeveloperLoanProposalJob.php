<?php

namespace App\Jobs\Developer;

use App\Http\Resources\LoanProposalResource;
use App\Models\LoanProposal;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetDeveloperLoanProposalJob
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

            // Find the loan proposal and ensure it belongs to one of the developer's projects
            $loanProposal = LoanProposal::with(['project', 'lender.user', 'documents'])
                ->withCount('documents')
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

            return response()->json([
                'success' => true,
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
