<?php

namespace App\Jobs\Documents;

use App\Enums\DocumentS3StatusEnum;
use App\Exceptions\Documents\FileNotFoundException;
use App\Exceptions\Documents\S3UploadException;
use App\Exceptions\Documents\S3VerificationException;
use App\Models\ProjectPhoto;
use Aws\S3\Exception\S3Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UploadProjectPhotoToS3Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 120, 300];

    public function __construct(
        public ProjectPhoto $photo
    ) {}

    public function handle(): void
    {
        try {
            $this->markPhotoAsProcessing();

            $localPath = $this->photo->file_path;
            $this->validateLocalFileExists($localPath);

            $fileContent = $this->readLocalFile($localPath);
            $s3Path = $this->buildS3Path();

            $this->uploadToS3($s3Path, $fileContent);
            $this->verifyS3Upload($s3Path);

            $this->updatePhotoWithS3Details($s3Path);
            $this->logSuccess();

        } catch (Throwable $e) {
            $this->handleException($e);
            throw $e;
        }
    }

    private function markPhotoAsProcessing(): void
    {
        $this->photo->update([
            's3_status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING,
        ]);
    }

    private function validateLocalFileExists(string $localPath): void
    {
        if (!Storage::disk('public')->exists($localPath)) {
            throw new FileNotFoundException($localPath);
        }
    }

    private function readLocalFile(string $localPath): string
    {
        $content = Storage::disk('public')->get($localPath);

        if ($content === null || $content === false) {
            throw new FileNotFoundException($localPath, "Failed to read file content");
        }

        return $content;
    }

    private function buildS3Path(): string
    {
        $projectUuid = $this->photo->project->uuid;
        $extension = pathinfo($this->photo->file_path, PATHINFO_EXTENSION);

        return "projects/{$projectUuid}/photos/{$this->photo->uuid}.{$extension}";
    }

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

    private function verifyS3Upload(string $s3Path): void
    {
        if (!Storage::disk('s3')->exists($s3Path)) {
            throw new S3VerificationException($s3Path);
        }
    }

    private function updatePhotoWithS3Details(string $s3Path): void
    {
        $this->photo->update([
            'storage_disk' => 's3',
            's3_path' => $s3Path,
            's3_url' => Storage::disk('s3')->url($s3Path),
            's3_bucket' => config('filesystems.disks.s3.bucket'),
            's3_status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL,
            'uploaded_to_s3_at' => now(),
        ]);
    }

    private function logSuccess(): void
    {
        Log::info('Project photo uploaded to S3 successfully', [
            'photo_id' => $this->photo->id,
            'file_name' => $this->photo->file_name,
            's3_path' => $this->photo->s3_path,
        ]);
    }

    private function handleException(Throwable $e): void
    {
        $isLastAttempt = $this->attempts() >= $this->tries;
        $status = $isLastAttempt
            ? DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED
            : DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING;

        $this->photo->update([
            's3_status' => $status,
        ]);

        Log::error('Project photo S3 upload failed', [
            'photo_id' => $this->photo->id,
            'attempt' => $this->attempts(),
            'max_attempts' => $this->tries,
            'error' => $e->getMessage(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        $this->photo->update([
            's3_status' => DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED,
        ]);

        Log::error('Project photo S3 upload failed permanently', [
            'photo_id' => $this->photo->id,
            'file_path' => $this->photo->file_path,
            'error' => $e->getMessage(),
        ]);
    }
}
