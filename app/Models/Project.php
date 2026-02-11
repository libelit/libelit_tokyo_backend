<?php

namespace App\Models;

use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'developer_id',
        'lender_id',
        'title',
        'description',
        'project_type',
        'city',
        'country',
        'address',
        'loan_amount',
        'currency',
        'min_investment',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'listed_at',
        'funded_at',
        'construction_start_date',
        'construction_end_date',
    ];

    protected $casts = [
        'project_type' => ProjectTypeEnum::class,
        'loan_amount' => 'decimal:2',
        'min_investment' => 'decimal:2',
        'status' => ProjectStatusEnum::class,
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'listed_at' => 'datetime',
        'funded_at' => 'datetime',
        'construction_start_date' => 'date',
        'construction_end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->uuid)) {
                $project->uuid = Str::uuid();
            }
        });
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(DeveloperProfile::class, 'developer_id');
    }

    public function lender(): BelongsTo
    {
        return $this->belongsTo(LenderProfile::class, 'lender_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProjectApproval::class);
    }

    public function spv(): HasOne
    {
        return $this->hasOne(Spv::class);
    }

    public function token(): HasOne
    {
        return $this->hasOne(Token::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function archives(): MorphMany
    {
        return $this->morphMany(DocumentArchive::class, 'archivable');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sequence');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProjectPhoto::class)->ordered();
    }

    public function featuredPhoto(): HasOne
    {
        return $this->hasOne(ProjectPhoto::class)->where('is_featured', true);
    }

    public function loanProposals(): HasMany
    {
        return $this->hasMany(LoanProposal::class);
    }

    /**
     * Get the latest successful project archive.
     */
    public function latestArchive()
    {
        return $this->morphOne(DocumentArchive::class, 'archivable')
            ->where('archive_type', 'project')
            ->successful()
            ->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
