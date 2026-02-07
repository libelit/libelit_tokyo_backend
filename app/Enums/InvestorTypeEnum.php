<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvestorTypeEnum: string implements HasLabel
{
    case TIER_1 = 'tier_1';
    case TIER_2 = 'tier_2';
    case TIER_3 = 'tier_3';

    public function getLabel(): string
    {
        return match ($this) {
            self::TIER_1 => 'Tier 1',
            self::TIER_2 => 'Tier 2',
            self::TIER_3 => 'Tier 3',
        };
    }
}
