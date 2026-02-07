<?php

namespace App\Jobs\Developer;

use App\Http\Resources\DeveloperProfileResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class UpdateProfileJob
{
    protected User $user;
    protected array $data;

    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;
            $developerProfile->update($this->data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => new DeveloperProfileResource($developerProfile->fresh()->load('user')),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
