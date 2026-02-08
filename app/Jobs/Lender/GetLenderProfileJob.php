<?php

namespace App\Jobs\Lender;

use App\Http\Resources\LenderProfileResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetLenderProfileJob
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile->load('user');

            return response()->json([
                'success' => true,
                'data' => new LenderProfileResource($lenderProfile),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
