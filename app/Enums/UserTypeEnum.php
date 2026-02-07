<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserTypeEnum: string implements HasLabel
{
    case ADMIN = 'admin';
    case DEVELOPER = 'developer';
    case INVESTOR = 'investor';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::DEVELOPER => 'Developer',
            self::INVESTOR => 'Investor',
        };
    }
}
