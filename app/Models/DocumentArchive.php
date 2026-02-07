<?php

namespace App\Models;

use App\Enums\DocumentS3StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentArchive extends Model
{
    protected $fillable = [
        'archivable_type',
        'archivable_id',
        'archive_type',
        'zip_file_name',
        'local_path',
        's3_path',
        's3_url',
        's3_bucket',
        'file_size',
        'file_hash',
        'hash_algorithm',
        'documents_count',
        'previous_hash',
        'status',
        'error_message',
        'archived_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'documents_count' => 'integer',
        'status' => DocumentS3StatusEnum::class,
        'archived_at' => 'datetime',
    ];

    /**
     * Get the parent archivable model (DeveloperProfile or Project).
     */
    public function archivable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all S3 upload records for this archive.
     */
    public function s3Uploads(): HasMany
    {
        return $this->hasMany(DocumentS3Upload::class, 'archive_id');
    }

    /**
     * Scope to filter by archive type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('archive_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, DocumentS3StatusEnum $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get successful archives.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS);
    }

    /**
     * Scope to get failed archives.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED);
    }

    /**
     * Check if archive was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === DocumentS3StatusEnum::ZIP_FILE_CREATION_SUCCESS;
    }

    /**
     * Check if archive failed.
     */
    public function isFailed(): bool
    {
        return $this->status === DocumentS3StatusEnum::ZIP_FILE_CREATION_FAILED;
    }

    /**
     * Check if archive is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === DocumentS3StatusEnum::ZIP_FILE_CREATION_PROCESSING;
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
}
