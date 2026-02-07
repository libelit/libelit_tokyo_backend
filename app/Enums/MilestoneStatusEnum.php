<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MilestoneStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case PROOF_SUBMITTED = 'proof_submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROOF_SUBMITTED => 'Proof Submitted',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PAID => 'Paid',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PROOF_SUBMITTED => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::PAID => 'success',
        };
    }
}
