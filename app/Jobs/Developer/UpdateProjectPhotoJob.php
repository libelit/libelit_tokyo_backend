<?php

namespace App\Jobs\Developer;

use App\Enums\ProjectStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class UpdateProjectPhotoJob
{
    protected User $user;
    protected int $projectId;
    protected int $photoId;
    protected array $data;

    public function __construct(User $user, int $projectId, int $photoId, array $data)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->photoId = $photoId;
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

            // Check if project is in editable state
            if (!in_array($project->status, [ProjectStatusEnum::DRAFT, ProjectStatusEnum::REJECTED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update photos for a project that is not in draft or rejected status.',
                ], 403);
            }

            $photo = $project->photos()->find($this->photoId);

            if (!$photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found.',
                ], 404);
            }

            // If setting as featured, unmark other featured photos
            if (isset($this->data['is_featured']) && $this->data['is_featured']) {
                $project->photos()->where('id', '!=', $photo->id)->update(['is_featured' => false]);
            }

            $photo->update([
                'title' => $this->data['title'] ?? $photo->title,
                'is_featured' => $this->data['is_featured'] ?? $photo->is_featured,
                'sort_order' => $this->data['sort_order'] ?? $photo->sort_order,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo updated successfully.',
                'data' => [
                    'id' => $photo->id,
                    'uuid' => $photo->uuid,
                    'file_url' => $photo->file_url,
                    'title' => $photo->title,
                    'is_featured' => $photo->is_featured,
                    'sort_order' => $photo->sort_order,
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
