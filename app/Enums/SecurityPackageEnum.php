<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SecurityPackageEnum: string implements HasLabel
{
    case MORTGAGE = 'mortgage';
    case SPV_CHARGE = 'spv_charge';
    case GUARANTEES = 'guarantees';

    public function getLabel(): string
    {
        return match ($this) {
            self::MORTGAGE => 'Mortgage',
            self::SPV_CHARGE => 'SPV Charge',
            self::GUARANTEES => 'Guarantees',
        };
    }
}
