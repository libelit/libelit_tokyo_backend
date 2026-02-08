<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentTypeEnum: string implements HasLabel
{
    // KYB Documents
    case KYB_CERTIFICATE = 'kyb_certificate';
    case KYB_ID = 'kyb_id';
    case KYB_ADDRESS_PROOF = 'kyb_address_proof';
    case KYB_FINANCIAL_STATEMENT = 'kyb_financial_statement';

    // Lender KYB Documents
    case KYB_LENDER_CERTIFICATE_OF_INCORPORATION = 'kyb_lender_certificate_of_incorporation';
    case KYB_LENDER_BUSINESS_LICENSE = 'kyb_lender_business_license';
    case KYB_LENDER_BENEFICIAL_OWNERSHIP = 'kyb_lender_beneficial_ownership';
    case KYB_LENDER_TAX_CERTIFICATE = 'kyb_lender_tax_certificate';
    case KYB_LENDER_ADDRESS_PROOF = 'kyb_lender_address_proof';

    // Project Documents (Legacy - for reference)
    case PROJECT_PROSPECTUS = 'project_prospectus';
    case PROJECT_LEGAL = 'project_legal';
    case PROJECT_VALUATION = 'project_valuation';
    case PROJECT_INSURANCE = 'project_insurance';
    case PROJECT_PERMIT = 'project_permit';

    // Loan Application Documents (Stage 1)
    case LOAN_DRAWINGS = 'loan_drawings';
    case LOAN_COST_CALCULATION = 'loan_cost_calculation';
    case LOAN_PHOTOS = 'loan_photos';
    case LOAN_LAND_TITLE = 'loan_land_title';
    case LOAN_BANK_STATEMENT = 'loan_bank_statement';
    case LOAN_REVENUE_EVIDENCE = 'loan_revenue_evidence';

    // SPV Documents
    case SPV_REGISTRATION = 'spv_registration';
    case SPV_COLLATERAL_DEED = 'spv_collateral_deed';

    // Contract Documents
    case CONTRACT_SIGNED = 'contract_signed';
    case CONTRACT_AMENDMENT = 'contract_amendment';

    // Monitoring Documents
    case MONITORING_REPORT = 'monitoring_report';
    case MONITORING_PHOTO = 'monitoring_photo';

    public function getLabel(): string
    {
        return match ($this) {
            self::KYB_CERTIFICATE => 'KYB Certificate',
            self::KYB_ID => 'KYB ID',
            self::KYB_ADDRESS_PROOF => 'KYB Address Proof',
            self::KYB_FINANCIAL_STATEMENT => 'KYB Financial Statement',
            self::KYB_LENDER_CERTIFICATE_OF_INCORPORATION => 'Certificate of Incorporation',
            self::KYB_LENDER_BUSINESS_LICENSE => 'Business/Financial Services License',
            self::KYB_LENDER_BENEFICIAL_OWNERSHIP => 'Beneficial Ownership Declaration',
            self::KYB_LENDER_TAX_CERTIFICATE => 'Tax Identification Certificate',
            self::KYB_LENDER_ADDRESS_PROOF => 'Proof of Business Address',
            self::PROJECT_PROSPECTUS => 'Project Prospectus',
            self::PROJECT_LEGAL => 'Project Legal',
            self::PROJECT_VALUATION => 'Project Valuation',
            self::PROJECT_INSURANCE => 'Project Insurance',
            self::PROJECT_PERMIT => 'Project Permit',
            self::LOAN_DRAWINGS => 'Architectural Drawings',
            self::LOAN_COST_CALCULATION => 'Cost Calculation',
            self::LOAN_PHOTOS => 'Site Photos',
            self::LOAN_LAND_TITLE => 'Land Title',
            self::LOAN_BANK_STATEMENT => 'Bank Statement',
            self::LOAN_REVENUE_EVIDENCE => 'Revenue Evidence',
            self::SPV_REGISTRATION => 'SPV Registration',
            self::SPV_COLLATERAL_DEED => 'SPV Collateral Deed',
            self::CONTRACT_SIGNED => 'Contract Signed',
            self::CONTRACT_AMENDMENT => 'Contract Amendment',
            self::MONITORING_REPORT => 'Monitoring Report',
            self::MONITORING_PHOTO => 'Monitoring Photo',
        };
    }
}
