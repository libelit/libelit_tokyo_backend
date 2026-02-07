<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvestmentStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case REFUNDED = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::COMPLETED => 'Completed',
            self::REFUNDED => 'Refunded',
        };
    }
}
