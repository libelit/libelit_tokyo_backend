<?php

namespace App\Models;

use App\Enums\TokenStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'spv_id',
        'xrpl_mpt_id',
        'xrpl_issuer_address',
        'name',
        'symbol',
        'total_supply',
        'issued_supply',
        'available_supply',
        'price_per_token',
        'decimals',
        'metadata_uri',
        'status',
        'minted_at',
    ];

    protected $casts = [
        'total_supply' => 'decimal:8',
        'issued_supply' => 'decimal:8',
        'available_supply' => 'decimal:8',
        'price_per_token' => 'decimal:8',
        'status' => TokenStatusEnum::class,
        'minted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function spv(): BelongsTo
    {
        return $this->belongsTo(Spv::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }
}
