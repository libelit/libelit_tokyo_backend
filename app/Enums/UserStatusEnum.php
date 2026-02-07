<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserStatusEnum: string implements HasLabel
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending',
        };
    }
}
