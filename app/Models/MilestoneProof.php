<?php

namespace App\Models;

use App\Enums\DocumentS3StatusEnum;
use App\Enums\MilestoneProofTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MilestoneProof extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'milestone_id',
        'proof_type',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'storage_disk',
        's3_path',
        's3_url',
        's3_bucket',
        's3_status',
        'uploaded_to_s3_at',
        'uploaded_by',
    ];

    protected $casts = [
        'proof_type' => MilestoneProofTypeEnum::class,
        's3_status' => DocumentS3StatusEnum::class,
        'uploaded_to_s3_at' => 'datetime',
    ];

    protected $appends = ['file_url'];

    /**
     * Get the milestone that owns the proof.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    /**
     * Get the user who uploaded the proof.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file URL (from S3 or local storage).
     */
    public function getFileUrlAttribute(): ?string
    {
        // Prefer S3 if available
        if ($this->storage_disk === 's3' && $this->s3_path) {
            return $this->getS3SignedUrl();
        }

        // Fall back to local storage
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    /**
     * Get a signed S3 URL for temporary access.
     */
    public function getS3SignedUrl(int $expirationMinutes = 60): ?string
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
            // Fall back to public URL
            return $this->s3_url;
        }
    }

    /**
     * Check if the proof is stored on S3.
     */
    public function isOnS3(): bool
    {
        return $this->storage_disk === 's3'
            && $this->s3_status === DocumentS3StatusEnum::FILE_UPLOAD_TO_S3_SUCCESSFUL;
    }
}
