<?php

namespace App\Jobs\Lender;

use App\Http\Resources\LoanProposalResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetLenderLoanProposalJob
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
            $lenderProfile = $this->user->lenderProfile;

            $loanProposal = $lenderProfile->loanProposals()
                ->with(['project.developer.user', 'lender.user', 'documents'])
                ->withCount('documents')
                ->find($this->loanProposalId);

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
