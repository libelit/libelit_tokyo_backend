<?php

namespace App\Jobs\Developer;

use App\Enums\ProjectStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DeleteProjectPhotoJob
{
    protected User $user;
    protected int $projectId;
    protected int $photoId;

    public function __construct(User $user, int $projectId, int $photoId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->photoId = $photoId;
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
                    'message' => 'Cannot delete photos from a project that is not in draft or rejected status.',
                ], 403);
            }

            $photo = $project->photos()->find($this->photoId);

            if (!$photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo not found.',
                ], 404);
            }

            // Delete from local storage
            if ($photo->file_path && Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }

            // Delete from S3 if exists
            if ($photo->s3_path && $photo->storage_disk === 's3') {
                try {
                    Storage::disk('s3')->delete($photo->s3_path);
                } catch (Exception $e) {
                    \Log::warning('Failed to delete photo from S3', [
                        'photo_id' => $photo->id,
                        's3_path' => $photo->s3_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
