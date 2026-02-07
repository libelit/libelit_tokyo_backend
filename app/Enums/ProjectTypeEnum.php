<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectTypeEnum: string implements HasLabel
{
    case RESIDENTIAL = 'residential';
    case COMMERCIAL = 'commercial';
    case MIXED_USE = 'mixed_use';
    case INDUSTRIAL = 'industrial';
    case LAND = 'land';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESIDENTIAL => 'Residential',
            self::COMMERCIAL => 'Commercial',
            self::MIXED_USE => 'Mixed Use',
            self::INDUSTRIAL => 'Industrial',
            self::LAND => 'Land',
        };
    }
}
