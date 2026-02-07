<?php

namespace App\Models;

use App\Enums\DocumentS3StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentS3Upload extends Model
{
    protected $fillable = [
        'document_id',
        'archive_id',
        'upload_type',
        'source_path',
        'destination_path',
        'file_name',
        'file_size',
        'mime_type',
        'status',
        'attempts',
        'max_attempts',
        'error_code',
        'error_message',
        'error_trace',
        'started_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'status' => DocumentS3StatusEnum::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the document this upload belongs to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the archive this upload belongs to.
     */
    public function archive(): BelongsTo
    {
        return $this->belongsTo(DocumentArchive::class, 'archive_id');
    }

    /**
     * Scope to filter by upload type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('upload_type', $type);
    }

    /**
     * Scope to filter document uploads.
     */
    public function scopeDocuments($query)
    {
        return $query->where('upload_type', 'document');
    }

    /**
     * Scope to filter archive uploads.
     */
    public function scopeArchives($query)
    {
        return $query->where('upload_type', 'archive');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, DocumentS3StatusEnum $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get successful uploads.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [
            DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL,
            DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS,
        ]);
    }

    /**
     * Scope to get failed uploads.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [
            DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED,
            DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED,
        ]);
    }

    /**
     * Scope to get processing uploads.
     */
    public function scopeProcessing($query)
    {
        return $query->whereIn('status', [
            DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING,
            DocumentS3StatusEnum::ZIP_FILE_CREATION_PROCESSING,
        ]);
    }

    /**
     * Scope to get pending uploads.
     */
    public function scopePending($query)
    {
        return $query->where('status', DocumentS3StatusEnum::PENDING);
    }

    /**
     * Check if upload is successful.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL,
            DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS,
        ]);
    }

    /**
     * Check if upload failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [
            DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED,
            DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED,
        ]);
    }

    /**
     * Check if upload is processing.
     */
    public function isProcessing(): bool
    {
        return in_array($this->status, [
            DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING,
            DocumentS3StatusEnum::ZIP_FILE_CREATION_PROCESSING,
        ]);
    }

    /**
     * Check if can retry.
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && $this->attempts < $this->max_attempts;
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get duration of upload.
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? $this->failed_at ?? now();
        $seconds = $this->started_at->diffInSeconds($endTime);

        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return "{$minutes}m {$remainingSeconds}s";
    }
}
