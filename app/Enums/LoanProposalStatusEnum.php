<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LoanProposalStatusEnum: string implements HasLabel
{
    case LOAN_PROPOSAL_SUBMITTED_BY_LENDER = 'submitted_by_lender';
    case LOAN_PROPOSAL_UNDER_REVIEW_BY_DEVELOPER = 'under_review_by_developer';
    case LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER = 'accepted_by_developer';
    case LOAN_PROPOSAL_REJECTED_BY_DEVELOPER = 'rejected_by_developer';
    case LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER = 'signed_by_developer';
    case LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER = 'signed_by_lender';
    case LOAN_TERM_AGREEMENT_FULLY_EXECUTED = 'loan_term_fully_executed';
    case LOAN_PROPOSAL_EXPIRED = 'loan_proposal_expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOAN_PROPOSAL_SUBMITTED_BY_LENDER => 'Submitted by Lender',
            self::LOAN_PROPOSAL_UNDER_REVIEW_BY_DEVELOPER => 'Under Review by Developer',
            self::LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER => 'Accepted by Developer',
            self::LOAN_PROPOSAL_REJECTED_BY_DEVELOPER => 'Rejected by Developer',
            self::LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER => 'Signed by Developer',
            self::LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER => 'Signed by Lender',
            self::LOAN_TERM_AGREEMENT_FULLY_EXECUTED => 'Loam Term Fully Executed',
            self::LOAN_PROPOSAL_EXPIRED => 'Loan Proposal Expired',
        };
    }
}
