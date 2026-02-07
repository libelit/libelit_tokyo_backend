<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum XrplTxStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case FAILED = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::VALIDATED => 'Validated',
            self::FAILED => 'Failed',
        };
    }
}
