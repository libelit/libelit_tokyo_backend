<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RiskToleranceEnum: string implements HasLabel
{
    case CONSERVATIVE = 'conservative';
    case MODERATE = 'moderate';
    case AGGRESSIVE = 'aggressive';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONSERVATIVE => 'Conservative',
            self::MODERATE => 'Moderate',
            self::AGGRESSIVE => 'Aggressive',
        };
    }
}
