<?php

namespace App\Jobs\Developer;

use App\Enums\ProjectStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DeleteProjectDocumentJob
{
    protected User $user;
    protected int $projectId;
    protected int $documentId;

    public function __construct(User $user, int $projectId, int $documentId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->documentId = $documentId;
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

            // Only allow deleting documents from draft projects
            if ($project->status !== ProjectStatusEnum::DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be deleted from draft projects.',
                ], 403);
            }

            $document = $project->documents()->find($this->documentId);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            // Delete the file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
