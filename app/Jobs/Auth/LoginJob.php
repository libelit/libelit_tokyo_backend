<?php

namespace App\Jobs\Auth;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginJob
{
    protected string $email;
    protected string $password;

    /**
     * Create a new job instance.
     */
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return JsonResponse
     */
    public function handle(): JsonResponse
    {
        try {
            $user = User::query()->where('email', $this->email)->first();
            if (!$user || !Hash::check($this->password, $user->password)) {
                $result = response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            } else {
                $token = $user->createToken('auth_token');
                $accessToken = $token->accessToken;
                $expiresAt = Carbon::parse($token->token->expires_at)->timestamp;
                $result = response()->json([
                    'success' => true,
                    'data' => [
                        'user'         => $user,
                        'access_token' => $accessToken,
                        'expires_at' => $expiresAt,
                    ],
                ]);
            }
        } catch (Exception $exception) {
            $result = response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
        return $result;
    }
}
