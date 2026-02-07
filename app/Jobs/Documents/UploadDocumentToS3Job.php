<?php

namespace App\Jobs\Documents;

use App\Enums\DocumentS3StatusEnum;
use App\Exceptions\Documents\FileNotFoundException;
use App\Exceptions\Documents\S3UploadException;
use App\Exceptions\Documents\S3VerificationException;
use App\Models\DeveloperProfile;
use App\Models\Document;
use App\Models\DocumentS3Upload;
use Aws\S3\Exception\S3Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UploadDocumentToS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 120, 300]; // 1min, 2min, 5min

    private ?DocumentS3Upload $uploadRecord = null;

    public function __construct(
        public Document $document
    ) {}

    public function handle(): void
    {
        try {
            $this->initializeUploadRecord();
            $this->markDocumentAsProcessing();

            $localPath = $this->getLocalPath();
            $this->validateLocalFileExists($localPath);

            $fileContent = $this->readLocalFile($localPath);
            $s3Path = $this->buildS3Path();

            $this->uploadToS3($s3Path, $fileContent);
            $this->verifyS3Upload($s3Path);

            $this->updateDocumentWithS3Details($s3Path);
            $this->markUploadAsSuccessful();

            $this->logSuccess();

        } catch (Throwable $e) {
            $this->handleException($e);
            throw $e;
        }
    }

    /**
     * Initialize the upload tracking record.
     */
    private function initializeUploadRecord(): void
    {
        $this->uploadRecord = DocumentS3Upload::create([
            'document_id' => $this->document->id,
            'upload_type' => 'document',
            'source_path' => $this->document->file_path,
            'destination_path' => $this->buildS3Path(),
            'file_name' => $this->document->file_name,
            'file_size' => $this->document->file_size,
            'mime_type' => $this->document->mime_type,
            'status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING,
            'attempts' => $this->attempts(),
            'max_attempts' => $this->tries,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the document as processing.
     */
    private function markDocumentAsProcessing(): void
    {
        $this->document->update([
            's3_status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING,
        ]);
    }

    /**
     * Get the local file path.
     */
    private function getLocalPath(): string
    {
        return $this->document->file_path;
    }

    /**
     * Validate that the local file exists.
     */
    private function validateLocalFileExists(string $localPath): void
    {
        if (!Storage::disk('public')->exists($localPath)) {
            throw new FileNotFoundException($localPath);
        }
    }

    /**
     * Read the local file content.
     */
    private function readLocalFile(string $localPath): string
    {
        $content = Storage::disk('public')->get($localPath);

        if ($content === null || $content === false) {
            throw new FileNotFoundException($localPath, "Failed to read file content");
        }

        return $content;
    }

    /**
     * Build the S3 path for the document.
     */
    private function buildS3Path(): string
    {
        $archiveType = $this->getArchiveType();
        $uuid = $this->document->uuid;
        $extension = pathinfo($this->document->file_path, PATHINFO_EXTENSION);
        $documentType = $this->document->document_type->value;

        return "documents/{$archiveType}/{$uuid}/{$documentType}.{$extension}";
    }

    /**
     * Upload file content to S3.
     */
    private function uploadToS3(string $s3Path, string $fileContent): void
    {
        try {
            $uploaded = Storage::disk('s3')->put($s3Path, $fileContent);

            if (!$uploaded) {
                throw new S3UploadException($s3Path);
            }
        } catch (S3Exception $e) {
            throw new S3UploadException($s3Path, "S3 SDK Error: " . $e->getMessage());
        }
    }

    /**
     * Verify that the file was uploaded to S3.
     */
    private function verifyS3Upload(string $s3Path): void
    {
        if (!Storage::disk('s3')->exists($s3Path)) {
            throw new S3VerificationException($s3Path);
        }
    }

    /**
     * Update the document record with S3 details.
     */
    private function updateDocumentWithS3Details(string $s3Path): void
    {
        $this->document->update([
            'storage_disk' => 's3',
            's3_path' => $s3Path,
            's3_url' => Storage::disk('s3')->url($s3Path),
            's3_bucket' => config('filesystems.disks.s3.bucket'),
            's3_status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL,
            'uploaded_to_s3_at' => now(),
        ]);
    }

    /**
     * Mark the upload record as successful.
     */
    private function markUploadAsSuccessful(): void
    {
        $this->uploadRecord?->update([
            'status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL,
            'completed_at' => now(),
        ]);
    }

    /**
     * Get the archive type based on the documentable model.
     */
    private function getArchiveType(): string
    {
        return $this->document->documentable_type === DeveloperProfile::class
            ? 'kyb'
            : 'project';
    }

    /**
     * Log successful upload.
     */
    private function logSuccess(): void
    {
        Log::info('Document uploaded to S3 successfully', [
            'document_id' => $this->document->id,
            'file_name' => $this->document->file_name,
            's3_path' => $this->document->s3_path,
        ]);
    }

    /**
     * Handle exceptions during upload.
     */
    private function handleException(Throwable $e): void
    {
        $errorCode = $this->getErrorCode($e);
        $isLastAttempt = $this->attempts() >= $this->tries;
        $status = $isLastAttempt
            ? DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED
            : DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING;

        $this->uploadRecord?->update([
            'status' => $status,
            'attempts' => $this->attempts(),
            'error_code' => $errorCode,
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'failed_at' => $isLastAttempt ? now() : null,
        ]);

        Log::error('Document S3 upload failed', [
            'document_id' => $this->document->id,
            'attempt' => $this->attempts(),
            'max_attempts' => $this->tries,
            'error_code' => $errorCode,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * Get error code from exception.
     */
    private function getErrorCode(Throwable $e): string
    {
        return match (true) {
            $e instanceof FileNotFoundException => 'FILE_NOT_FOUND',
            $e instanceof S3UploadException => 'S3_UPLOAD_FAILED',
            $e instanceof S3VerificationException => 'S3_VERIFICATION_FAILED',
            $e instanceof S3Exception => 'S3_SDK_ERROR',
            default => 'UNKNOWN_ERROR',
        };
    }

    /**
     * Handle job failure after all retries exhausted.
     */
    public function failed(Throwable $e): void
    {
        $this->document->update([
            's3_status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED,
        ]);

        $this->uploadRecord?->update([
            'status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED,
            'failed_at' => now(),
            'error_code' => $this->getErrorCode($e),
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
        ]);

        Log::error('Document S3 upload failed permanently', [
            'document_id' => $this->document->id,
            'file_path' => $this->document->file_path,
            'error' => $e->getMessage(),
        ]);
    }
}
