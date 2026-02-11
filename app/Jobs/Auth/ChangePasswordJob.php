<?php

namespace App\Jobs\Auth;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ChangePasswordJob
{
    protected User $user;
    protected string $password;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function handle(): JsonResponse
    {
        try {
            $this->user->update([
                'password' => Hash::make($this->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
