<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TokenStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case MINTED = 'minted';
    case ACTIVE = 'active';
    case FROZEN = 'frozen';
    case BURNED = 'burned';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::MINTED => 'Minted',
            self::ACTIVE => 'Active',
            self::FROZEN => 'Frozen',
            self::BURNED => 'Burned',
        };
    }
}
