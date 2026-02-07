<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MilestoneProofTypeEnum: string implements HasLabel
{
    case PHOTO = 'photo';
    case INVOICE = 'invoice';
    case INSPECTION_REPORT = 'inspection_report';
    case BANK_STATEMENT = 'bank_statement';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::PHOTO => 'Photo',
            self::INVOICE => 'Invoice',
            self::INSPECTION_REPORT => 'Inspection Report',
            self::BANK_STATEMENT => 'Bank Statement',
            self::OTHER => 'Other Document',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::PHOTO => 'Site photos showing progress',
            self::INVOICE => 'Paid invoices for materials/labor',
            self::INSPECTION_REPORT => 'Third-party inspection documents',
            self::BANK_STATEMENT => 'Payment transaction records',
            self::OTHER => 'Any other supporting documents',
        };
    }

    public function getAcceptedFormats(): string
    {
        return match ($this) {
            self::PHOTO => '.jpg,.jpeg,.png,.webp',
            self::INVOICE => '.pdf,.jpg,.jpeg,.png',
            self::INSPECTION_REPORT => '.pdf,.doc,.docx',
            self::BANK_STATEMENT => '.pdf',
            self::OTHER => '.pdf,.jpg,.jpeg,.png,.doc,.docx',
        };
    }
}
