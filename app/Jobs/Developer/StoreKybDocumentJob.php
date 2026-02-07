<?php

namespace App\Jobs\Developer;

use App\Enums\DocumentS3StatusEnum;
use App\Enums\KybStatusEnum;
use App\Enums\VerificationStatusEnum;
use App\Http\Resources\DocumentResource;
use App\Jobs\Documents\UploadDocumentToS3Job;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreKybDocumentJob
{
    protected User $user;
    protected array $documents;

    public function __construct(User $user, array $documents)
    {
        $this->user = $user;
        $this->documents = $documents;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            // Check if KYB is already approved
            if ($developerProfile->kyb_status === KybStatusEnum::APPROVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYB verification is already approved. You cannot upload new documents.',
                ], 403);
            }

            $uploadedDocuments = [];

            DB::transaction(function () use ($developerProfile, &$uploadedDocuments) {
                foreach ($this->documents as $docData) {
                    $documentType = $docData['document_type'];
                    $title = $docData['title'];
                    $file = $docData['file'];

                    // Check if document type already exists
                    $existingDocument = $developerProfile->documents()
                        ->where('document_type', $documentType)
                        ->first();

                    if ($existingDocument) {
                        // Delete the old file from local and S3 if exists
                        $this->deleteExistingDocument($existingDocument);
                    }

                    // Store the file locally first
                    $uuid = Str::uuid()->toString();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $documentType . '.' . $extension;
                    $filePath = $file->storeAs('documents/kyb/' . $uuid, $fileName, 'public');

                    // Create document record
                    $document = $developerProfile->documents()->create([
                        'uuid' => $uuid,
                        'document_type' => $documentType,
                        'title' => $title,
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_by' => $this->user->id,
                        'verification_status' => VerificationStatusEnum::PENDING,
                        'storage_disk' => 'local',
                        's3_status' => DocumentS3StatusEnum::PENDING,
                    ]);

                    // Dispatch async job to upload to S3
                    UploadDocumentToS3Job::dispatch($document)->delay(now()->addSeconds(5));

                    $uploadedDocuments[] = $document;
                }
            });

            return response()->json([
                'success' => true,
                'message' => count($uploadedDocuments) . ' document(s) uploaded successfully.',
                'data' => DocumentResource::collection($uploadedDocuments),
            ], 201);

        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an existing document from local and S3 storage.
     */
    private function deleteExistingDocument($document): void
    {
        // Delete from local storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Delete from S3 if exists
        if ($document->s3_path && $document->storage_disk === 's3') {
            try {
                Storage::disk('s3')->delete($document->s3_path);
            } catch (Exception $e) {
                // Log but don't fail - the document will be soft deleted anyway
                \Log::warning('Failed to delete document from S3', [
                    'document_id' => $document->id,
                    's3_path' => $document->s3_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $document->delete();
    }
}
