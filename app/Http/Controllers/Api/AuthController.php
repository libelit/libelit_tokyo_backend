<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\Auth\ChangePasswordJob;
use App\Jobs\Auth\LoginJob;
use App\Jobs\Auth\RegisterJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $job = new RegisterJob(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            userType: $request->validated('type'),
            companyName: $request->validated('company_name')
        );
        return $job->handle();
    }

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $job = new LoginJob(
            email: $request->validated('email'),
            password: $request->validated('password')
        );
        return $job->handle();
    }

    /**
     * @return JsonResponse|void
     */
    public function logout(Request $request)
    {
        if (Auth::user()) {
            $request->user()->token()->revoke();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        }
    }

    /**
     * Change the user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $job = new ChangePasswordJob(
            user: $request->user(),
            password: $request->validated('password')
        );
        return $job->handle();
    }
}
