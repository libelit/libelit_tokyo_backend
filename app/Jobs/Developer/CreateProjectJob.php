<?php

namespace App\Jobs\Developer;

use App\Enums\KybStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\ProjectResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CreateProjectJob
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
            if ($developerProfile->kyb_status !== KybStatusEnum::APPROVED) {
                $result = response()->json([
                    'success' => false,
                    'message' => 'You must complete KYB verification before creating projects.',
                ], 403);
            } else {
                $project = $developerProfile->projects()->create([
                    'uuid' => Str::uuid(),
                    'title' => $this->data['title'],
                    'description' => $this->data['description'] ?? null,
                    'project_type' => $this->data['project_type'],
                    'address' => $this->data['address'] ?? null,
                    'city' => $this->data['city'] ?? null,
                    'country' => $this->data['country'] ?? null,
                    'funding_goal' => $this->data['funding_goal'],
                    'min_investment' => $this->data['min_investment'],
                    'expected_return' => $this->data['expected_return'],
                    'loan_term_months' => $this->data['loan_term_months'],
                    'ltv_ratio' => $this->data['ltv_ratio'] ?? null,
                    'status' => ProjectStatusEnum::DRAFT,
                ]);

                $result =  response()->json([
                    'success' => true,
                    'message' => 'Project created successfully.',
                    'data' => new ProjectResource($project),
                ], 201);
            }
        } catch (Exception $exception) {
            $result =  response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
        return $result;
    }
}
