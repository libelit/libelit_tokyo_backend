<?php

namespace App\Jobs\Lender;

use App\Models\BlockchainAuditLog;
use App\Models\LenderProfile;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListLenderAuditLogsJob
{
    protected User $user;
    protected int $perPage;

    public function __construct(
        User $user,
        int $perPage = 15
    ) {
        $this->user = $user;
        $this->perPage = $perPage;
    }

    public function handle(): JsonResponse
    {
        try {
            $profile = $this->user->lenderProfile()->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lender profile not found.',
                ], 404);
            }

            $result = BlockchainAuditLog::query()
                ->where('auditable_id', $profile->id)
                ->where('auditable_type', (new LenderProfile)->getMorphClass())
                ->paginate($this->perPage);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (Exception $exception) {
            report($exception);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit logs.',
            ], 500);
        }
    }
}
