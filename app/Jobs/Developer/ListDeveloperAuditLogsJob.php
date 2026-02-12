<?php

namespace App\Jobs\Developer;

use App\Models\BlockchainAuditLog;
use App\Models\DeveloperProfile;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListDeveloperAuditLogsJob
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
            $profile = $this->user->developerProfile()->first();

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Developer profile not found.',
                ], 404);
            }

            $result = BlockchainAuditLog::query()
                ->where('auditable_id', $profile->id)
                ->where('auditable_type', (new DeveloperProfile)->getMorphClass())
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
