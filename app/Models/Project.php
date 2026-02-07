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
        'title',
        'description',
        'project_type',
        'city',
        'country',
        'address',
        'funding_goal',
        'currency',
        'min_investment',
        'expected_return',
        'loan_term_months',
        'ltv_ratio',
        'risk_score',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'listed_at',
        'funded_at',
        'completed_at',
    ];

    protected $casts = [
        'project_type' => ProjectTypeEnum::class,
        'funding_goal' => 'decimal:2',
        'min_investment' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'ltv_ratio' => 'decimal:2',
        'risk_score' => 'integer',
        'status' => ProjectStatusEnum::class,
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'listed_at' => 'datetime',
        'funded_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
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
