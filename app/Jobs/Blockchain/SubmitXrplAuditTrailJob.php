<?php

namespace App\Jobs\Blockchain;

use App\Enums\XrplTxStatusEnum;
use App\Models\BlockchainAuditLog;
use App\Services\XrplService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmitXrplAuditTrailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [60, 300, 900, 3600, 14400]; // 1min, 5min, 15min, 1hr, 4hr

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BlockchainAuditLog $auditLog
    ) {}

    /**
     * Execute the job.
     */
    public function handle(XrplService $xrplService): void
    {
        // Update attempt tracking
        $this->auditLog->update([
            'attempts' => $this->auditLog->attempts + 1,
            'last_attempt_at' => now(),
        ]);

        // Check if XRPL is configured
        if (!$xrplService->isConfigured()) {
            Log::warning('XRPL not configured, skipping audit trail submission', [
                'audit_log_id' => $this->auditLog->id,
            ]);

            $this->auditLog->update([
                'error_message' => 'XRPL credentials not configured',
            ]);

            // Don't retry if not configured
            $this->delete();
            return;
        }

        // Submit to XRPL
        $result = $xrplService->submitAuditHash($this->auditLog);

        if ($result['success']) {
            $this->auditLog->update([
                'tx_hash' => $result['tx_hash'],
                'xrpl_transaction_id' => $result['xrpl_transaction_id'],
                'status' => XrplTxStatusEnum::VALIDATED,
                'submitted_at' => now(),
                'validated_at' => now(),
                'error_message' => null,
            ]);

            Log::info('Audit trail submitted to XRPL', [
                'audit_log_id' => $this->auditLog->id,
                'event_type' => $this->auditLog->event_type->value,
                'tx_hash' => $result['tx_hash'],
            ]);
        } else {
            $this->auditLog->update([
                'error_message' => $result['error'],
            ]);

            // Throw exception to trigger retry
            throw new \Exception($result['error']);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $e): void
    {
        $this->auditLog->update([
            'status' => XrplTxStatusEnum::FAILED,
            'error_message' => $e->getMessage(),
        ]);

        Log::error('Audit trail XRPL submission failed permanently', [
            'audit_log_id' => $this->auditLog->id,
            'event_type' => $this->auditLog->event_type->value,
            'attempts' => $this->auditLog->attempts,
            'error' => $e->getMessage(),
        ]);
    }
}
