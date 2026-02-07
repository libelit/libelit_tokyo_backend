<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AmlStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case CLEARED = 'cleared';
    case FLAGGED = 'flagged';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CLEARED => 'Cleared',
            self::FLAGGED => 'Flagged',
        };
    }
}
