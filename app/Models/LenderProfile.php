<?php

namespace App\Models;

use App\Enums\AccreditationStatusEnum;
use App\Enums\AmlStatusEnum;
use App\Enums\LenderTypeEnum;
use App\Enums\KybStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LenderProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lender_profiles';

    protected $fillable = [
        'user_id',
        'lender_type',
        'company_name',
        'address',
        'kyb_status',
        'kyb_submitted_at',
        'kyb_approved_at',
        'kyb_approved_by',
        'kyb_rejection_reason',
        'aml_status',
        'aml_checked_at',
        'accreditation_status',
        'accreditation_expires_at',
        'is_active',
    ];

    protected $casts = [
        'lender_type' => LenderTypeEnum::class,
        'kyb_status' => KybStatusEnum::class,
        'kyb_submitted_at' => 'datetime',
        'kyb_approved_at' => 'datetime',
        'aml_status' => AmlStatusEnum::class,
        'aml_checked_at' => 'datetime',
        'accreditation_status' => AccreditationStatusEnum::class,
        'accreditation_expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kyb_approved_by');
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class, 'lender_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'lender_id');
    }

    public function loanProposals(): HasMany
    {
        return $this->hasMany(LoanProposal::class, 'lender_id');
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
