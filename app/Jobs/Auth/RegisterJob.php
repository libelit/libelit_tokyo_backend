<?php

namespace App\Jobs\Auth;

use App\Enums\UserTypeEnum;
use App\Models\DeveloperProfile;
use App\Models\InvestorProfile;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegisterJob
{
    protected string $name;
    protected string $email;
    protected string $password;
    protected string $userType;
    protected string $companyName;

    /**
     * Create a new job instance.
     */
    public function __construct(string $name, string $email, string $password, string $userType, string $companyName)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->userType = $userType;
        $this->companyName = $companyName;
    }

    /**
     * Execute the job.
     *
     * @return JsonResponse
     */
    public function handle(): JsonResponse
    {
        try {
            $user = DB::transaction(function () {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => $this->password,
                    'type' => $this->userType,
                ]);

                $this->createUserProfile($user);

                return $user;
            });

            $result = response()->json([
                'success' => true,
                'message' => 'User registered successfully.',
                'data' => [
                    'user' => $user,
                ],
            ], 201);
        } catch (Exception $exception) {
            $result = response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }

        return $result;
    }

    /**
     * Create the appropriate profile based on user type.
     */
    protected function createUserProfile(User $user): void
    {
        match ($user->type) {
            UserTypeEnum::INVESTOR => InvestorProfile::query()->create(['user_id' => $user->id, 'company_name' => $this->companyName]),
            UserTypeEnum::DEVELOPER => DeveloperProfile::query()->create(['user_id' => $user->id, 'company_name' => $this->companyName]),
            default => null,
        };
    }
}
