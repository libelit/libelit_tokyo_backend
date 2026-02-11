<?php

namespace App\Managers;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\XrplTxStatusEnum;
use App\Jobs\Blockchain\SubmitXrplAuditTrailJob;
use App\Models\BlockchainAuditLog;
use App\Models\DeveloperProfile;
use App\Models\LenderProfile;
use App\Models\LoanProposal;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditTrailManager
{
    /**
     * Record an event to the blockchain audit trail.
     *
     * Usage:
     *   AuditTrailManager::record('developer_kyb_approved', $developer, ['approved_by' => 1]);
     *   AuditTrailManager::record(BlockchainAuditEventTypeEnum::PROJECT_SUBMITTED, $project);
     */
    public static function record(
        string|BlockchainAuditEventTypeEnum $eventType,
        Model $entity,
        array $additionalData = []
    ): ?BlockchainAuditLog {
        try {
            // Convert string to enum if needed
            if (is_string($eventType)) {
                $eventType = BlockchainAuditEventTypeEnum::from($eventType);
            }

            // Build event data
            $eventData = self::buildEventData($eventType, $entity, $additionalData);

            // Generate hash
            $dataHash = BlockchainAuditLog::generateDataHash($eventData);

            // Create audit log
            $auditLog = BlockchainAuditLog::create([
                'event_type' => $eventType,
                'auditable_type' => get_class($entity),
                'auditable_id' => $entity->getKey(),
                'user_id' => Auth::id(),
                'event_data' => $eventData,
                'data_hash' => $dataHash,
                'status' => XrplTxStatusEnum::PENDING,
                'attempts' => 0,
            ]);

            // Dispatch async job with small delay for database consistency
            SubmitXrplAuditTrailJob::dispatch($auditLog)
                ->delay(now()->addSeconds(5));

            Log::info('Audit trail event recorded', [
                'audit_log_id' => $auditLog->id,
                'event_type' => $eventType->value,
                'entity_type' => class_basename($entity),
                'entity_id' => $entity->getKey(),
            ]);

            return $auditLog;

        } catch (\Exception $e) {
            Log::error('Failed to record audit trail event', [
                'event_type' => is_string($eventType) ? $eventType : $eventType->value,
                'entity_type' => get_class($entity),
                'entity_id' => $entity->getKey(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build the event data payload for hashing.
     */
    private static function buildEventData(
        BlockchainAuditEventTypeEnum $eventType,
        Model $entity,
        array $additionalData
    ): array {
        $baseData = [
            'event_type' => $eventType->value,
            'entity_type' => class_basename($entity),
            'entity_id' => $entity->getKey(),
            'timestamp' => now()->toIso8601String(),
        ];

        // Add entity-specific fields based on model type
        $entityData = self::getEntityData($entity);

        return array_merge($baseData, $entityData, $additionalData);
    }

    /**
     * Get entity-specific data for inclusion in the audit hash.
     */
    private static function getEntityData(Model $entity): array
    {
        return match (true) {
            $entity instanceof DeveloperProfile => [
                'company_name' => $entity->company_name,
                'company_registration_number' => $entity->company_registration_number,
                'user_id' => $entity->user_id,
                'kyb_status' => $entity->kyb_status?->value,
            ],

            $entity instanceof LenderProfile => [
                'company_name' => $entity->company_name,
                'user_id' => $entity->user_id,
                'kyb_status' => $entity->kyb_status?->value,
            ],

            $entity instanceof Project => [
                'uuid' => $entity->uuid,
                'title' => $entity->title,
                'developer_id' => $entity->developer_id,
                'lender_id' => $entity->lender_id,
                'loan_amount' => (string) $entity->loan_amount,
                'currency' => $entity->currency,
                'status' => $entity->status?->value,
            ],

            $entity instanceof LoanProposal => [
                'project_id' => $entity->project_id,
                'lender_id' => $entity->lender_id,
                'proposed_amount' => (string) ($entity->proposed_amount ?? $entity->amount ?? 0),
                'interest_rate' => (string) ($entity->interest_rate ?? 0),
                'status' => $entity->status?->value,
            ],

            $entity instanceof ProjectMilestone => [
                'project_id' => $entity->project_id,
                'title' => $entity->title,
                'amount' => (string) $entity->amount,
                'sequence' => $entity->sequence,
                'status' => $entity->status?->value,
            ],

            default => [],
        };
    }
}
