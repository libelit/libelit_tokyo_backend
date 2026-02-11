<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum XrplTxTypeEnum: string implements HasLabel
{
    case PAYMENT = 'Payment';
    case ACCOUNT_SET = 'AccountSet';
    case ESCROW_CREATE = 'EscrowCreate';
    case ESCROW_FINISH = 'EscrowFinish';
    case ESCROW_CANCEL = 'EscrowCancel';
    case NFTOKEN_MINT = 'NFTokenMint';
    case TRUST_SET = 'TrustSet';
    case OFFER_CREATE = 'OfferCreate';
    case OFFER_CANCEL = 'OfferCancel';
    case MPT_ISSUANCE_CREATE = 'MPTIssuanceCreate';
    case MPT_ISSUANCE_SET = 'MPTIssuanceSet';
    case MPT_TOKEN_AUTHORIZE = 'MPTTokenAuthorize';

    public function getLabel(): string
    {
        return match ($this) {
            self::PAYMENT => 'Payment',
            self::ACCOUNT_SET => 'Account Set',
            self::ESCROW_CREATE => 'Escrow Create',
            self::ESCROW_FINISH => 'Escrow Finish',
            self::ESCROW_CANCEL => 'Escrow Cancel',
            self::NFTOKEN_MINT => 'NFToken Mint',
            self::TRUST_SET => 'Trust Set',
            self::OFFER_CREATE => 'Offer Create',
            self::OFFER_CANCEL => 'Offer Cancel',
            self::MPT_ISSUANCE_CREATE => 'MPT Issuance Create',
            self::MPT_ISSUANCE_SET => 'MPT Issuance Set',
            self::MPT_TOKEN_AUTHORIZE => 'MPT Token Authorize',
        };
    }
}
