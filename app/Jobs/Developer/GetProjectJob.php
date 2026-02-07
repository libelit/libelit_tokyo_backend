<?php

namespace App\Jobs\Developer;

use App\Http\Resources\ProjectResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetProjectJob
{
    protected User $user;
    protected int $projectId;

    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()
                ->with('documents')
                ->withCount('documents')
                ->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ProjectResource($project),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
