<?php

namespace App\Jobs\Developer;

use App\Http\Resources\DeveloperProfileResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetProfileJob
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile->load('user');

            return response()->json([
                'success' => true,
                'data' => new DeveloperProfileResource($developerProfile),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
