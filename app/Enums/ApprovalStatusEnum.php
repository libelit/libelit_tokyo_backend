<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ApprovalStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case REVISION_REQUESTED = 'revision_requested';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::REVISION_REQUESTED => 'Revision Requested',
        };
    }
}
