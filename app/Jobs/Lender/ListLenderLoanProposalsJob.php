<?php

namespace App\Jobs\Lender;

use App\Http\Resources\LoanProposalResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListLenderLoanProposalsJob
{
    protected User $user;
    protected ?string $status;
    protected int $perPage;

    public function __construct(User $user, ?string $status = null, int $perPage = 15)
    {
        $this->user = $user;
        $this->status = $status;
        $this->perPage = $perPage;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            $query = $lenderProfile->loanProposals()
                ->with(['project.developer.user', 'documents'])
                ->withCount('documents')
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($this->status && $this->status !== 'all') {
                $query->where('status', $this->status);
            }

            $loanProposals = $query->paginate($this->perPage);

            return response()->json([
                'success' => true,
                'data' => LoanProposalResource::collection($loanProposals),
                'meta' => [
                    'current_page' => $loanProposals->currentPage(),
                    'last_page' => $loanProposals->lastPage(),
                    'per_page' => $loanProposals->perPage(),
                    'total' => $loanProposals->total(),
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
