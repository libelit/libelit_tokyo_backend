<?php

namespace App\Models;

use App\Enums\CollateralTypeEnum;
use App\Enums\SpvStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Spv extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'registration_number',
        'jurisdiction',
        'collateral_type',
        'collateral_description',
        'collateral_value',
        'status',
        'created_at_blockchain',
    ];

    protected $casts = [
        'collateral_type' => CollateralTypeEnum::class,
        'collateral_value' => 'decimal:2',
        'status' => SpvStatusEnum::class,
        'created_at_blockchain' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function token(): HasOne
    {
        return $this->hasOne(Token::class);
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
}
