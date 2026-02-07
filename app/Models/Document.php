<?php

namespace App\Models;

use App\Enums\DocumentS3StatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\VerificationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->uuid)) {
                $document->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'documentable_type',
        'documentable_id',
        'document_type',
        'title',
        'file_path',
        'storage_disk',
        's3_path',
        's3_url',
        's3_bucket',
        's3_status',
        'uploaded_to_s3_at',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_by',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'expires_at',
        'is_public',
    ];

    protected $casts = [
        'document_type' => DocumentTypeEnum::class,
        'verification_status' => VerificationStatusEnum::class,
        's3_status' => DocumentS3StatusEnum::class,
        'verified_at' => 'datetime',
        'uploaded_to_s3_at' => 'datetime',
        'expires_at' => 'date',
        'is_public' => 'boolean',
        'file_size' => 'integer',
    ];

    protected $appends = ['file_url'];

    /**
     * Get the parent documentable model (DeveloperProfile or Project).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who verified the document.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get all S3 upload records for this document.
     */
    public function s3Uploads(): HasMany
    {
        return $this->hasMany(DocumentS3Upload::class);
    }

    /**
     * Get the URL to access the document file.
     * Returns S3 signed URL if on S3, otherwise local URL.
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->storage_disk === 's3' && $this->s3_path) {
            return $this->getS3SignedUrl();
        }

        // Fallback to local storage
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    /**
     * Generate a signed URL for S3 access.
     */
    public function getS3SignedUrl(int $expirationMinutes = 30): ?string
    {
        if (!$this->s3_path) {
            return null;
        }

        try {
            return Storage::disk('s3')->temporaryUrl(
                $this->s3_path,
                now()->addMinutes($expirationMinutes)
            );
        } catch (\Exception $e) {
            // If signed URL fails, return the direct URL
            return $this->s3_url;
        }
    }

    /**
     * Check if document is stored on S3.
     */
    public function isOnS3(): bool
    {
        return $this->storage_disk === 's3' && !empty($this->s3_path);
    }

    /**
     * Check if document is stored locally.
     */
    public function isLocal(): bool
    {
        return $this->storage_disk === 'local';
    }

    /**
     * Check if S3 upload was successful.
     */
    public function isS3UploadSuccessful(): bool
    {
        return $this->s3_status === DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL;
    }

    /**
     * Check if S3 upload failed.
     */
    public function isS3UploadFailed(): bool
    {
        return $this->s3_status === DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_FAILED;
    }

    /**
     * Check if S3 upload is processing.
     */
    public function isS3UploadProcessing(): bool
    {
        return $this->s3_status === DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_PROCESSING;
    }

    /**
     * Scope to filter documents on S3.
     */
    public function scopeOnS3($query)
    {
        return $query->where('storage_disk', 's3');
    }

    /**
     * Scope to filter documents on local storage.
     */
    public function scopeLocal($query)
    {
        return $query->where('storage_disk', 'local');
    }

    /**
     * Scope to filter by S3 status.
     */
    public function scopeWithS3Status($query, DocumentS3StatusEnum $status)
    {
        return $query->where('s3_status', $status);
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
