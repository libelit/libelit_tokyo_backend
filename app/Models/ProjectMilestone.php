<?php

namespace App\Models;

use App\Enums\MilestoneStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMilestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'sequence',
        'amount',
        'percentage',
        'status',
        'due_date',
        'proof_submitted_at',
        'approved_at',
        'approved_by',
        'paid_at',
        'payment_reference',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'status' => MilestoneStatusEnum::class,
        'due_date' => 'date',
        'proof_submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the project that owns the milestone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who approved the milestone.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the proofs for the milestone.
     */
    public function proofs(): HasMany
    {
        return $this->hasMany(MilestoneProof::class, 'milestone_id');
    }

    /**
     * Scope for pending milestones.
     */
    public function scopePending($query)
    {
        return $query->where('status', MilestoneStatusEnum::PENDING);
    }

    /**
     * Scope for completed milestones (approved or paid).
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            MilestoneStatusEnum::APPROVED,
            MilestoneStatusEnum::PAID,
        ]);
    }

    /**
     * Check if milestone can be completed (proofs uploaded and submitted).
     * Only pending or rejected milestones can be completed.
     */
    public function canComplete(): bool
    {
        return in_array($this->status, [
            MilestoneStatusEnum::PENDING,
            MilestoneStatusEnum::REJECTED,
        ]);
    }

    /**
     * Calculate percentage from project loan amount.
     */
    public function calculatePercentage(): float
    {
        $loanAmount = $this->project?->loan_amount ?? 0;
        if ($loanAmount <= 0) {
            return 0;
        }
        return round(($this->amount / $loanAmount) * 100, 2);
    }
}
