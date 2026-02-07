<?php

namespace App\Models;

use App\Enums\InvestmentStatusEnum;
use App\Enums\PaymentMethodEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'project_id',
        'investor_id',
        'token_id',
        'amount',
        'token_quantity',
        'payment_method',
        'payment_currency',
        'payment_reference',
        'xrpl_tx_hash',
        'status',
        'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'token_quantity' => 'decimal:8',
        'payment_method' => PaymentMethodEnum::class,
        'status' => InvestmentStatusEnum::class,
        'confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($investment) {
            if (empty($investment->uuid)) {
                $investment->uuid = Str::uuid();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
