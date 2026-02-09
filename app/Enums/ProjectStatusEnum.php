<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProjectStatusEnum: string implements HasLabel
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case PROPOSAL_ACCEPTED = 'proposal_accepted';
    case REJECTED = 'rejected';
    case FUNDING = 'funding';
    case FUNDED = 'funded';
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::PROPOSAL_ACCEPTED => 'Proposal Accepted',
            self::REJECTED => 'Rejected',
            self::FUNDING => 'Funding',
            self::FUNDED => 'Funded',
            self::COMPLETED => 'Completed',
        };
    }
}
