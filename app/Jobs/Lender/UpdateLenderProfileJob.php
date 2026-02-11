<?php

namespace App\Jobs\Lender;

use App\Http\Resources\LenderProfileResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class UpdateLenderProfileJob
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
            // Separate user fields from profile fields
            $userFields = ['name', 'phone'];
            $userData = Arr::only($this->data, $userFields);
            $profileData = Arr::except($this->data, $userFields);

            // Update user data if provided
            if (!empty($userData)) {
                $this->user->update($userData);
            }

            // Update profile data if provided
            $lenderProfile = $this->user->lenderProfile;
            if (!empty($profileData)) {
                $lenderProfile->update($profileData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => new LenderProfileResource($lenderProfile->fresh()->load('user')),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
