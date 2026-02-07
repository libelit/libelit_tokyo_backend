<?php

namespace App\Models;

use App\Enums\ApprovalStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'status',
        'reviewer_id',
        'ai_risk_assessment',
        'ltv_assessment',
        'notes',
        'rejection_reason',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => ApprovalStatusEnum::class,
        'ai_risk_assessment' => 'array',
        'ltv_assessment' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
