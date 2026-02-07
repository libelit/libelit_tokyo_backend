<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SpvStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case DISSOLVED = 'dissolved';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::DISSOLVED => 'Dissolved',
        };
    }
}
