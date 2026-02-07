<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethodEnum: string implements HasLabel
{
    case XRPL_ESCROW = 'xrpl_escrow';
    case STABLECOIN = 'stablecoin';
    case FIAT = 'fiat';
    case XRPL_NATIVE = 'xrpl_native';

    public function getLabel(): string
    {
        return match ($this) {
            self::XRPL_ESCROW => 'XRPL Escrow',
            self::STABLECOIN => 'Stablecoin',
            self::FIAT => 'Fiat',
            self::XRPL_NATIVE => 'XRPL Native',
        };
    }
}
