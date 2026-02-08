<?php

namespace App\Jobs\Developer;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListProjectPhotosJob
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

            $project = $developerProfile->projects()->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            $photos = $project->photos()
                ->ordered()
                ->get()
                ->map(fn ($photo) => [
                    'id' => $photo->id,
                    'uuid' => $photo->uuid,
                    'file_url' => $photo->file_url,
                    'file_name' => $photo->file_name,
                    'file_size' => $photo->formatted_file_size,
                    'mime_type' => $photo->mime_type,
                    'title' => $photo->title,
                    'is_featured' => $photo->is_featured,
                    'sort_order' => $photo->sort_order,
                    's3_status' => $photo->s3_status,
                    'created_at' => $photo->created_at,
                ]);

            return response()->json([
                'success' => true,
                'data' => $photos,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
