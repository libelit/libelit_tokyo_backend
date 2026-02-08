<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum KycStatusEnum: string implements HasLabel
{
    case NOT_STARTED = 'not_started';
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Not Started',
            self::PENDING => 'Pending',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
