<?php

namespace App\Models;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\XrplTxStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BlockchainAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'auditable_type',
        'auditable_id',
        'user_id',
        'event_data',
        'data_hash',
        'tx_hash',
        'xrpl_transaction_id',
        'status',
        'attempts',
        'last_attempt_at',
        'error_message',
        'submitted_at',
        'validated_at',
    ];

    protected $casts = [
        'event_type' => BlockchainAuditEventTypeEnum::class,
        'event_data' => 'array',
        'status' => XrplTxStatusEnum::class,
        'last_attempt_at' => 'datetime',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    /**
     * Get the auditable entity.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who triggered the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated XRPL transaction.
     */
    public function xrplTransaction(): BelongsTo
    {
        return $this->belongsTo(XrplTransaction::class);
    }

    /**
     * Scope for pending audit logs that need submission.
     */
    public function scopePending($query)
    {
        return $query->where('status', XrplTxStatusEnum::PENDING)
            ->whereNull('tx_hash');
    }

    /**
     * Scope for submitted but not yet validated transactions.
     */
    public function scopeAwaitingValidation($query)
    {
        return $query->where('status', XrplTxStatusEnum::PENDING)
            ->whereNotNull('tx_hash')
            ->whereNull('validated_at');
    }

    /**
     * Scope for failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', XrplTxStatusEnum::FAILED);
    }

    /**
     * Scope for validated transactions.
     */
    public function scopeValidated($query)
    {
        return $query->where('status', XrplTxStatusEnum::VALIDATED);
    }

    /**
     * Generate SHA-256 hash from event data.
     */
    public static function generateDataHash(array $eventData): string
    {
        // Sort keys recursively for consistent hashing
        $sortedData = self::sortArrayRecursively($eventData);
        $json = json_encode($sortedData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return hash('sha256', $json);
    }

    /**
     * Sort array keys recursively.
     */
    private static function sortArrayRecursively(array $array): array
    {
        ksort($array);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::sortArrayRecursively($value);
            }
        }
        return $array;
    }

    /**
     * Get the explorer URL for this transaction.
     */
    public function getExplorerUrlAttribute(): ?string
    {
        if (!$this->tx_hash) {
            return null;
        }

        $isTestnet = config('xrpl.testnet', true);
        $baseUrl = $isTestnet
            ? 'https://testnet.xrpl.org/transactions/'
            : 'https://livenet.xrpl.org/transactions/';

        return $baseUrl . $this->tx_hash;
    }
}
