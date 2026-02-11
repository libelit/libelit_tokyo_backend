<?php

namespace App\Console\Commands;

use App\Enums\XrplTxStatusEnum;
use App\Models\BlockchainAuditLog;
use App\Services\XrplService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyPendingXrplTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'xrpl:verify-pending-transactions
                            {--limit=100 : Maximum number of transactions to check}';

    /**
     * The console command description.
     */
    protected $description = 'Verify pending XRPL audit trail transactions';

    /**
     * Execute the console command.
     */
    public function handle(XrplService $xrplService): int
    {
        if (!$xrplService->isConfigured()) {
            $this->warn('XRPL not configured. Skipping verification.');
            return Command::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        // Get pending transactions that have been submitted but not validated
        $pendingLogs = BlockchainAuditLog::awaitingValidation()
            ->where('submitted_at', '<', now()->subMinutes(2)) // Wait 2 min after submission
            ->limit($limit)
            ->get();

        $this->info("Checking {$pendingLogs->count()} pending transactions...");

        $validated = 0;
        $failed = 0;
        $errors = 0;

        foreach ($pendingLogs as $auditLog) {
            $result = $xrplService->verifyTransaction($auditLog->tx_hash);

            if (!$result['success']) {
                $this->warn("Failed to verify tx_hash: {$auditLog->tx_hash}");
                $errors++;
                continue;
            }

            if ($result['validated']) {
                $auditLog->update([
                    'status' => XrplTxStatusEnum::VALIDATED,
                    'validated_at' => now(),
                ]);

                // Also update the XrplTransaction record if it exists
                if ($auditLog->xrplTransaction) {
                    $auditLog->xrplTransaction->update([
                        'status' => XrplTxStatusEnum::VALIDATED,
                        'ledger_index' => $result['ledger_index'],
                        'validated_at' => now(),
                        'raw_response' => $result['raw_response'],
                    ]);
                }

                $validated++;
                $this->line("  Validated: {$auditLog->tx_hash}");
            }
        }

        // Check for stale transactions (submitted > 10 min ago, still not validated)
        $staleCount = BlockchainAuditLog::awaitingValidation()
            ->where('submitted_at', '<', now()->subMinutes(10))
            ->update(['status' => XrplTxStatusEnum::FAILED]);

        if ($staleCount > 0) {
            $failed += $staleCount;
            $this->warn("Marked {$staleCount} stale transactions as failed");
        }

        $this->info("Completed: {$validated} validated, {$failed} failed, {$errors} errors");

        Log::info('XRPL verification command completed', [
            'checked' => $pendingLogs->count(),
            'validated' => $validated,
            'failed' => $failed,
            'errors' => $errors,
        ]);

        return Command::SUCCESS;
    }
}
