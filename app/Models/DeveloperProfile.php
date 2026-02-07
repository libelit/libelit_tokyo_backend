<?php

namespace App\Models;

use App\Enums\KybStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeveloperProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_registration_number',
        'address',
        'kyb_status',
        'kyb_submitted_at',
        'kyb_approved_at',
        'kyb_approved_by',
        'kyb_rejection_reason',
    ];

    protected $casts = [
        'kyb_status' => KybStatusEnum::class,
        'kyb_submitted_at' => 'datetime',
        'kyb_approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kyb_approved_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'developer_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'walletable');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function archives(): MorphMany
    {
        return $this->morphMany(DocumentArchive::class, 'archivable');
    }

    /**
     * Get the latest successful KYB archive.
     */
    public function latestArchive()
    {
        return $this->morphOne(DocumentArchive::class, 'archivable')
            ->where('archive_type', 'kyb')
            ->successful()
            ->latest();
    }
}
