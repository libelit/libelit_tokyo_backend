<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CollateralTypeEnum: string implements HasLabel
{
    case PROPERTY = 'property';
    case LAND = 'land';
    case MIXED = 'mixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PROPERTY => 'Property',
            self::LAND => 'Land',
            self::MIXED => 'Mixed',
        };
    }
}
