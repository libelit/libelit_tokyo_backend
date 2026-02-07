<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AuditActionEnum: string implements HasLabel
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';
    case STATUS_CHANGED = 'status_changed';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case LOGIN = 'login';
    case LOGOUT = 'logout';

    public function getLabel(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::UPDATED => 'Updated',
            self::DELETED => 'Deleted',
            self::RESTORED => 'Restored',
            self::STATUS_CHANGED => 'Status Changed',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::LOGIN => 'Login',
            self::LOGOUT => 'Logout',
        };
    }
}
