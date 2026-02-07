<?php

namespace App\Jobs\Developer;

use App\Enums\KybStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DeleteKybDocumentJob
{
    protected User $user;
    protected int $documentId;

    public function __construct(User $user, int $documentId)
    {
        $this->user = $user;
        $this->documentId = $documentId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            // Check if KYB is already approved or under review
            if (in_array($developerProfile->kyb_status, [KybStatusEnum::APPROVED, KybStatusEnum::UNDER_REVIEW])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete documents when KYB is approved or under review.',
                ], 403);
            }

            $document = $developerProfile->documents()->find($this->documentId);

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
