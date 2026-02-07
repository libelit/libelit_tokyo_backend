<?php

namespace App\Jobs\Documents;

use App\Enums\DocumentS3StatusEnum;
use App\Exceptions\Documents\FileNotFoundException;
use App\Exceptions\Documents\S3UploadException;
use App\Exceptions\Documents\S3VerificationException;
use App\Exceptions\Documents\ZipCreationException;
use App\Models\Document;
use App\Models\DocumentArchive;
use App\Models\DocumentS3Upload;
use App\Models\DeveloperProfile;
use App\Models\Project;
use Aws\S3\Exception\S3Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class CreateDocumentArchiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [120, 300, 600]; // 2min, 5min, 10min

    private ?DocumentArchive $archive = null;
    private ?DocumentS3Upload $uploadRecord = null;
    private ?string $tempDir = null;
    private ?string $zipPath = null;
    private array $localFoldersToDelete = [];

    public function __construct(
        public Model $archivable,
        public string $archiveType
    ) {}

    public function handle(): void
    {
        try {
            $documents = $this->getDocumentsToArchive();

            if ($documents->isEmpty()) {
                $this->logNoDocuments();
                return;
            }

            $this->createArchiveRecord($documents->count());
            $this->createTempDirectory();

            $this->copyDocumentsFromLocal($documents);

            $zipName = $this->generateZipFileName();
            $this->createZipFile($zipName);

            $hash = $this->generateFileHash();
            $fileSize = $this->getZipFileSize();

            $s3Path = $this->buildArchiveS3Path($zipName);
            $this->initializeUploadRecord($s3Path, $zipName, $fileSize);

            $this->uploadArchiveToS3($s3Path);
            $this->verifyS3Upload($s3Path);

            $this->updateArchiveWithS3Details($s3Path, $hash, $fileSize);
            $this->markUploadAsSuccessful();

            // Delete local document folders after zip is successfully uploaded to S3
            $this->deleteLocalDocumentFolders();

            $this->cleanup();
            $this->logSuccess($hash, $documents->count());

        } catch (Throwable $e) {
            $this->handleException($e);
            $this->cleanup();
            throw $e;
        }
    }

    /**
     * Get documents that are already on S3 for this entity.
     */
    private function getDocumentsToArchive(): Collection
    {
        return Document::where('documentable_type', get_class($this->archivable))
            ->where('documentable_id', $this->archivable->id)
            ->where('s3_status', DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL)
            ->get();
    }

    /**
     * Log when no documents to archive.
     */
    private function logNoDocuments(): void
    {
        Log::info('No documents to archive', [
            'archivable_type' => get_class($this->archivable),
            'archivable_id' => $this->archivable->id,
            'archive_type' => $this->archiveType,
        ]);
    }

    /**
     * Create the archive record in the database.
     */
    private function createArchiveRecord(int $documentsCount): void
    {
        $previousArchive = $this->getPreviousArchive();

        $this->archive = DocumentArchive::create([
            'archivable_type' => get_class($this->archivable),
            'archivable_id' => $this->archivable->id,
            'archive_type' => $this->archiveType,
            'zip_file_name' => '', // Will be updated later
            'documents_count' => $documentsCount,
            'previous_hash' => $previousArchive?->file_hash,
            'status' => DocumentS3StatusEnum::ZIP_FILE_CREATION_PROCESSING,
        ]);
    }

    /**
     * Get the previous successful archive for hash chaining.
     */
    private function getPreviousArchive(): ?DocumentArchive
    {
        return DocumentArchive::where('archivable_type', get_class($this->archivable))
            ->where('archivable_id', $this->archivable->id)
            ->where('archive_type', $this->archiveType)
            ->where('status', DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS)
            ->latest()
            ->first();
    }

    /**
     * Create temporary directory for archive creation.
     */
    private function createTempDirectory(): void
    {
        $this->tempDir = storage_path("app/temp/archives/{$this->archive->id}");

        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Copy all documents from local storage to temp directory.
     */
    private function copyDocumentsFromLocal(Collection $documents): void
    {
        foreach ($documents as $document) {
            $this->copySingleDocumentFromLocal($document);
        }
    }

    /**
     * Copy a single document from local storage to temp directory.
     */
    private function copySingleDocumentFromLocal(Document $document): void
    {
        $localPath = $document->file_path;

        if (!Storage::disk('public')->exists($localPath)) {
            throw new FileNotFoundException($localPath);
        }

        $content = Storage::disk('public')->get($localPath);

        if ($content === null || $content === false) {
            throw new FileNotFoundException($localPath, "Failed to read file content");
        }

        // Use document type as filename to avoid conflicts
        $extension = pathinfo($localPath, PATHINFO_EXTENSION);
        $fileName = $document->document_type->value . '.' . $extension;
        $tempFilePath = "{$this->tempDir}/{$fileName}";
        file_put_contents($tempFilePath, $content);

        // Track the local folder for deletion after zip upload
        $localFolder = dirname($localPath);
        if (!in_array($localFolder, $this->localFoldersToDelete)) {
            $this->localFoldersToDelete[] = $localFolder;
        }
    }

    /**
     * Generate the zip file name.
     */
    private function generateZipFileName(): string
    {
        return "{$this->archiveType}_archive_" . time() . ".zip";
    }

    /**
     * Create the zip file from downloaded documents.
     */
    private function createZipFile(string $zipName): void
    {
        $this->zipPath = storage_path("app/temp/{$zipName}");

        $zip = new ZipArchive();
        $result = $zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new ZipCreationException($this->zipPath, "Failed to create zip archive, error code: {$result}");
        }

        $this->addFilesToZip($zip);

        if (!$zip->close()) {
            throw new ZipCreationException($this->zipPath, "Failed to close zip archive");
        }

        // Update archive record with zip file name
        $this->archive->update(['zip_file_name' => $zipName]);
    }

    /**
     * Add files from temp directory to zip.
     */
    private function addFilesToZip(ZipArchive $zip): void
    {
        $files = glob("{$this->tempDir}/*");

        if (empty($files)) {
            throw new ZipCreationException($this->zipPath, "No files found in temp directory");
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                $zip->addFile($file, basename($file));
            }
        }
    }

    /**
     * Generate SHA256 hash of the zip file.
     */
    private function generateFileHash(): string
    {
        return hash_file('sha256', $this->zipPath);
    }

    /**
     * Get the zip file size.
     */
    private function getZipFileSize(): int
    {
        return filesize($this->zipPath);
    }

    /**
     * Build the S3 path for the archive.
     */
    private function buildArchiveS3Path(string $zipName): string
    {
        return "archives/{$this->archiveType}/{$this->archivable->id}/{$zipName}";
    }

    /**
     * Initialize the upload tracking record.
     */
    private function initializeUploadRecord(string $s3Path, string $zipName, int $fileSize): void
    {
        $this->uploadRecord = DocumentS3Upload::create([
            'archive_id' => $this->archive->id,
            'upload_type' => 'archive',
            'source_path' => $this->zipPath,
            'destination_path' => $s3Path,
            'file_name' => $zipName,
            'file_size' => $fileSize,
            'mime_type' => 'application/zip',
            'status' => DocumentS3StatusEnum::ZIP_FILE_CREATION_PROCESSING,
            'attempts' => $this->attempts(),
            'max_attempts' => $this->tries,
            'started_at' => now(),
        ]);
    }

    /**
     * Upload the archive to S3.
     */
    private function uploadArchiveToS3(string $s3Path): void
    {
        try {
            $uploaded = Storage::disk('s3')->put($s3Path, file_get_contents($this->zipPath));

            if (!$uploaded) {
                throw new S3UploadException($s3Path);
            }
        } catch (S3Exception $e) {
            throw new S3UploadException($s3Path, "S3 SDK Error: " . $e->getMessage());
        }
    }

    /**
     * Verify that the archive was uploaded to S3.
     */
    private function verifyS3Upload(string $s3Path): void
    {
        if (!Storage::disk('s3')->exists($s3Path)) {
            throw new S3VerificationException($s3Path);
        }
    }

    /**
     * Update the archive record with S3 details.
     */
    private function updateArchiveWithS3Details(string $s3Path, string $hash, int $fileSize): void
    {
        $this->archive->update([
            's3_path' => $s3Path,
            's3_url' => Storage::disk('s3')->url($s3Path),
            's3_bucket' => config('filesystems.disks.s3.bucket'),
            'file_size' => $fileSize,
            'file_hash' => $hash,
            'status' => DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS,
            'archived_at' => now(),
        ]);
    }

    /**
     * Mark the upload record as successful.
     */
    private function markUploadAsSuccessful(): void
    {
        $this->uploadRecord?->update([
            'status' => DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS,
            'completed_at' => now(),
        ]);
    }

    /**
     * Delete local document folders after zip is uploaded to S3.
     */
    private function deleteLocalDocumentFolders(): void
    {
        foreach ($this->localFoldersToDelete as $folder) {
            try {
                if (Storage::disk('public')->exists($folder)) {
                    Storage::disk('public')->deleteDirectory($folder);
                }
            } catch (Throwable $e) {
                Log::warning('Failed to delete local document folder', [
                    'folder' => $folder,
                    'archive_id' => $this->archive?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Clean up temporary files and directories.
     */
    private function cleanup(): void
    {
        // Delete zip file
        if ($this->zipPath && file_exists($this->zipPath)) {
            @unlink($this->zipPath);
        }

        // Delete temp directory
        if ($this->tempDir && is_dir($this->tempDir)) {
            $this->deleteDirectory($this->tempDir);
        }
    }

    /**
     * Recursively delete a directory.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }

        @rmdir($dir);
    }

    /**
     * Log successful archive creation.
     */
    private function logSuccess(string $hash, int $documentsCount): void
    {
        Log::info('Document archive created successfully', [
            'archive_id' => $this->archive->id,
            'archivable_type' => get_class($this->archivable),
            'archivable_id' => $this->archivable->id,
            'archive_type' => $this->archiveType,
            'file_hash' => $hash,
            'documents_count' => $documentsCount,
        ]);
    }

    /**
     * Handle exceptions during archive creation.
     */
    private function handleException(Throwable $e): void
    {
        $errorCode = $this->getErrorCode($e);
        $isLastAttempt = $this->attempts() >= $this->tries;
        $status = $isLastAttempt
            ? DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED
            : DocumentS3StatusEnum::ZIP_FILE_CREATION_PROCESSING;

        $this->archive?->update([
            'status' => $status,
            'error_message' => $e->getMessage(),
        ]);

        $this->uploadRecord?->update([
            'status' => $status,
            'attempts' => $this->attempts(),
            'error_code' => $errorCode,
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'failed_at' => $isLastAttempt ? now() : null,
        ]);

        Log::error('Document archive creation failed', [
            'archive_id' => $this->archive?->id,
            'archivable_type' => get_class($this->archivable),
            'archivable_id' => $this->archivable->id,
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
            $e instanceof FileNotFoundException => 'LOCAL_FILE_NOT_FOUND',
            $e instanceof S3UploadException => 'S3_UPLOAD_FAILED',
            $e instanceof S3VerificationException => 'S3_VERIFICATION_FAILED',
            $e instanceof ZipCreationException => 'ZIP_CREATION_FAILED',
            $e instanceof S3Exception => 'S3_SDK_ERROR',
            default => 'UNKNOWN_ERROR',
        };
    }

    /**
     * Handle job failure after all retries exhausted.
     */
    public function failed(Throwable $e): void
    {
        $this->archive?->update([
            'status' => DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED,
            'error_message' => $e->getMessage(),
        ]);

        $this->uploadRecord?->update([
            'status' => DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED,
            'failed_at' => now(),
            'error_code' => $this->getErrorCode($e),
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
        ]);

        Log::error('Document archive creation failed permanently', [
            'archive_id' => $this->archive?->id,
            'archivable_type' => get_class($this->archivable),
            'archivable_id' => $this->archivable->id,
            'archive_type' => $this->archiveType,
            'error' => $e->getMessage(),
        ]);

        $this->cleanup();
    }
}
