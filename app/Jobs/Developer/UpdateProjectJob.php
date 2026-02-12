<?php

namespace App\Jobs\Developer;

use App\Enums\ProjectStatusEnum;
use App\Http\Resources\ProjectResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class UpdateProjectJob
{
    protected User $user;
    protected int $projectId;
    protected array $data;

    public function __construct(User $user, int $projectId, array $data)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->data = $data;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            // Only allow editing draft, submitted, or rejected projects
            if (!in_array($project->status, [ProjectStatusEnum::DRAFT, ProjectStatusEnum::SUBMITTED, ProjectStatusEnum::REJECTED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft, submitted, or rejected projects can be edited.',
                ], 403);
            }

            // If project was rejected, reset to draft when editing
            $updateData = $this->data;
            if ($project->status === ProjectStatusEnum::REJECTED) {
                $updateData['status'] = ProjectStatusEnum::DRAFT;
                $updateData['rejection_reason'] = null;
            }

            $project->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully.',
                'data' => new ProjectResource($project->fresh()),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
