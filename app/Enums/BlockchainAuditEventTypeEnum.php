<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BlockchainAuditEventTypeEnum: string implements HasLabel, HasColor
{
    // Developer KYB Events
    case DEVELOPER_KYB_SUBMITTED = 'developer_kyb_submitted';
    case DEVELOPER_KYB_APPROVED = 'developer_kyb_approved';
    case DEVELOPER_KYB_REJECTED = 'developer_kyb_rejected';

    // Lender KYB Events
    case LENDER_KYB_SUBMITTED = 'lender_kyb_submitted';
    case LENDER_KYB_APPROVED = 'lender_kyb_approved';
    case LENDER_KYB_REJECTED = 'lender_kyb_rejected';

    // Project Events
    case PROJECT_CREATED = 'project_created';
    case PROJECT_SUBMITTED = 'project_submitted';
    case PROJECT_LISTED = 'project_listed';
    case PROJECT_COMPLETED = 'project_completed';

    // Loan Proposal Events
    case LOAN_PROPOSAL_SUBMITTED = 'loan_proposal_submitted';
    case LOAN_PROPOSAL_ACCEPTED = 'loan_proposal_accepted';
    case LOAN_PROPOSAL_REJECTED = 'loan_proposal_rejected';
    case CONTRACT_SIGNED_BY_DEVELOPER = 'contract_signed_by_developer';
    case CONTRACT_SIGNED_BY_LENDER = 'contract_signed_by_lender';
    case LOAN_FULLY_EXECUTED = 'loan_fully_executed';

    // Milestone Events
    case MILESTONE_PAYMENT_REQUESTED = 'milestone_payment_requested';
    case MILESTONE_APPROVED = 'milestone_approved';
    case MILESTONE_REJECTED = 'milestone_rejected';
    case PAYMENT_CONFIRMED = 'payment_confirmed';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEVELOPER_KYB_SUBMITTED => 'Developer KYB Submitted',
            self::DEVELOPER_KYB_APPROVED => 'Developer KYB Approved',
            self::DEVELOPER_KYB_REJECTED => 'Developer KYB Rejected',
            self::LENDER_KYB_SUBMITTED => 'Lender KYB Submitted',
            self::LENDER_KYB_APPROVED => 'Lender KYB Approved',
            self::LENDER_KYB_REJECTED => 'Lender KYB Rejected',
            self::PROJECT_CREATED => 'Project Created',
            self::PROJECT_SUBMITTED => 'Project Submitted',
            self::PROJECT_LISTED => 'Project Listed',
            self::PROJECT_COMPLETED => 'Project Completed',
            self::LOAN_PROPOSAL_SUBMITTED => 'Loan Proposal Submitted',
            self::LOAN_PROPOSAL_ACCEPTED => 'Loan Proposal Accepted',
            self::LOAN_PROPOSAL_REJECTED => 'Loan Proposal Rejected',
            self::CONTRACT_SIGNED_BY_DEVELOPER => 'Contract Signed by Developer',
            self::CONTRACT_SIGNED_BY_LENDER => 'Contract Signed by Lender',
            self::LOAN_FULLY_EXECUTED => 'Loan Fully Executed',
            self::MILESTONE_PAYMENT_REQUESTED => 'Milestone Payment Requested',
            self::MILESTONE_APPROVED => 'Milestone Approved',
            self::MILESTONE_REJECTED => 'Milestone Rejected',
            self::PAYMENT_CONFIRMED => 'Payment Confirmed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DEVELOPER_KYB_SUBMITTED,
            self::LENDER_KYB_SUBMITTED,
            self::PROJECT_SUBMITTED,
            self::LOAN_PROPOSAL_SUBMITTED,
            self::MILESTONE_PAYMENT_REQUESTED => 'warning',

            self::DEVELOPER_KYB_APPROVED,
            self::LENDER_KYB_APPROVED,
            self::PROJECT_LISTED,
            self::LOAN_PROPOSAL_ACCEPTED,
            self::CONTRACT_SIGNED_BY_DEVELOPER,
            self::CONTRACT_SIGNED_BY_LENDER,
            self::LOAN_FULLY_EXECUTED,
            self::MILESTONE_APPROVED,
            self::PAYMENT_CONFIRMED,
            self::PROJECT_COMPLETED => 'success',

            self::DEVELOPER_KYB_REJECTED,
            self::LENDER_KYB_REJECTED,
            self::LOAN_PROPOSAL_REJECTED,
            self::MILESTONE_REJECTED => 'danger',

            self::PROJECT_CREATED => 'info',
        };
    }

    public function getEntityType(): string
    {
        return match ($this) {
            self::DEVELOPER_KYB_SUBMITTED,
            self::DEVELOPER_KYB_APPROVED,
            self::DEVELOPER_KYB_REJECTED => 'DeveloperProfile',

            self::LENDER_KYB_SUBMITTED,
            self::LENDER_KYB_APPROVED,
            self::LENDER_KYB_REJECTED => 'LenderProfile',

            self::PROJECT_CREATED,
            self::PROJECT_SUBMITTED,
            self::PROJECT_LISTED,
            self::PROJECT_COMPLETED => 'Project',

            self::LOAN_PROPOSAL_SUBMITTED,
            self::LOAN_PROPOSAL_ACCEPTED,
            self::LOAN_PROPOSAL_REJECTED,
            self::CONTRACT_SIGNED_BY_DEVELOPER,
            self::CONTRACT_SIGNED_BY_LENDER,
            self::LOAN_FULLY_EXECUTED => 'LoanProposal',

            self::MILESTONE_PAYMENT_REQUESTED,
            self::MILESTONE_APPROVED,
            self::MILESTONE_REJECTED,
            self::PAYMENT_CONFIRMED => 'ProjectMilestone',
        };
    }
}
