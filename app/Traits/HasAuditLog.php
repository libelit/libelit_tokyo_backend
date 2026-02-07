<?php

namespace App\Traits;

use App\Enums\AuditActionEnum;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            $model->logAudit(AuditActionEnum::CREATED, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();

            if (!empty($newValues)) {
                $model->logAudit(AuditActionEnum::UPDATED, $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit(AuditActionEnum::DELETED, $model->getAttributes(), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logAudit(AuditActionEnum::RESTORED, null, $model->getAttributes());
            });
        }
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    protected function logAudit(AuditActionEnum $action, ?array $oldValues = null, ?array $newValues = null, ?string $notes = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    public function logStatusChange(string $oldStatus, string $newStatus, ?string $notes = null): void
    {
        $this->logAudit(
            AuditActionEnum::STATUS_CHANGED,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            $notes
        );
    }

    public function logApproval(?string $notes = null): void
    {
        $this->logAudit(AuditActionEnum::APPROVED, null, null, $notes);
    }

    public function logRejection(?string $notes = null): void
    {
        $this->logAudit(AuditActionEnum::REJECTED, null, null, $notes);
    }
}
