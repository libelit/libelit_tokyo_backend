<?php

namespace App\Models;

use App\Enums\LoanProposalStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LoanProposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'project_id',
        'lender_id',
        'loan_amount_offered',
        'currency',
        'interest_rate',
        'loan_maturity_date',
        'security_packages',
        'max_ltv_accepted',
        'bid_expiry_date',
        'additional_conditions',
        'status',
        'rejection_reason',
        'accepted_at',
        'developer_signed_at',
        'lender_signed_at',
    ];

    protected $casts = [
        'loan_amount_offered' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'max_ltv_accepted' => 'decimal:2',
        'loan_maturity_date' => 'date',
        'bid_expiry_date' => 'date',
        'security_packages' => 'array',
        'status' => LoanProposalStatusEnum::class,
        'accepted_at' => 'datetime',
        'developer_signed_at' => 'datetime',
        'lender_signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loanProposal) {
            if (empty($loanProposal->uuid)) {
                $loanProposal->uuid = Str::uuid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lender(): BelongsTo
    {
        return $this->belongsTo(LenderProfile::class, 'lender_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
