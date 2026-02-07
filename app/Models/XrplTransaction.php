<?php

namespace App\Models;

use App\Enums\XrplTxStatusEnum;
use App\Enums\XrplTxTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XrplTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tx_hash',
        'tx_type',
        'from_address',
        'to_address',
        'amount',
        'currency',
        'fee',
        'sequence',
        'ledger_index',
        'status',
        'related_type',
        'related_id',
        'raw_response',
        'validated_at',
    ];

    protected $casts = [
        'tx_type' => XrplTxTypeEnum::class,
        'amount' => 'decimal:8',
        'fee' => 'decimal:8',
        'status' => XrplTxStatusEnum::class,
        'raw_response' => 'array',
        'validated_at' => 'datetime',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
