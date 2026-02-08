<?php

namespace App\Jobs\Verification;

use App\Config\VerificationConfig;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DeleteVerificationDocumentJob
{
    protected User $user;
    protected int $documentId;
    protected string $verificationType;

    public function __construct(User $user, int $documentId, string $verificationType)
    {
        $this->user = $user;
        $this->documentId = $documentId;
        $this->verificationType = $verificationType;
    }

    public function handle(): JsonResponse
    {
        try {
            $config = VerificationConfig::get($this->verificationType);
            $profile = VerificationConfig::getProfile($this->user, $this->verificationType);
            $statusField = $config['status_field'];
            $statusApproved = $config['status_approved'];
            $statusUnderReview = $config['status_under_review'];
            $label = $config['label'];

            // Check if verification is already approved or under review
            if (in_array($profile->$statusField, [$statusApproved, $statusUnderReview])) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete documents when {$label} is approved or under review.",
                ], 403);
            }

            $document = $profile->documents()->find($this->documentId);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            // Delete the file from local storage
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete from S3 if exists
            if ($document->s3_path && $document->storage_disk === 's3') {
                try {
                    Storage::disk('s3')->delete($document->s3_path);
                } catch (Exception $e) {
                    \Log::warning('Failed to delete document from S3', [
                        'document_id' => $document->id,
                        's3_path' => $document->s3_path,
                        'error' => $e->getMessage(),
                    ]);
                }
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
