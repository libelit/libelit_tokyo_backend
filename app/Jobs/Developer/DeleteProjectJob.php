<?php

namespace App\Jobs\Developer;

use App\Enums\ProjectStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DeleteProjectJob
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

            // Only allow deleting draft projects
            if ($project->status !== ProjectStatusEnum::DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft projects can be deleted.',
                ], 403);
            }

            // Delete all project documents and their files
            foreach ($project->documents as $document) {
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }
            }

            $project->documents()->delete();
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
