<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AccreditationStatusEnum: string implements HasLabel
{
    case PENDING  = 'pending';
    case VERIFIED = 'verified';
    case EXPIRED  = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::EXPIRED => 'Expired',
        };
    }
}
