<?php

namespace App\Config;

use App\Enums\DocumentTypeEnum;
use App\Enums\KybStatusEnum;
use App\Enums\KycStatusEnum;
use App\Models\DeveloperProfile;
use App\Models\LenderProfile;

class VerificationConfig
{
    public const TYPE_KYB = 'kyb';
    public const TYPE_KYC = 'kyc';

    /**
     * Get configuration for a verification type.
     */
    public static function get(string $type): array
    {
        return match ($type) {
            self::TYPE_KYB => self::getKybConfig(),
            self::TYPE_KYC => self::getKycConfig(),
            default => throw new \InvalidArgumentException("Unknown verification type: {$type}"),
        };
    }

    /**
     * KYB configuration for developers.
     */
    protected static function getKybConfig(): array
    {
        return [
            'profile_class' => DeveloperProfile::class,
            'profile_relation' => 'developerProfile',
            'status_enum' => KybStatusEnum::class,
            'status_field' => 'kyb_status',
            'submitted_at_field' => 'kyb_submitted_at',
            'storage_path' => 'documents/kyb',
            'archive_type' => 'kyb',
            'allowed_document_types' => [
                DocumentTypeEnum::KYB_CERTIFICATE,
                DocumentTypeEnum::KYB_ID,
                DocumentTypeEnum::KYB_ADDRESS_PROOF,
            ],
            'required_document_types' => [
                DocumentTypeEnum::KYB_CERTIFICATE,
                DocumentTypeEnum::KYB_ID,
                DocumentTypeEnum::KYB_ADDRESS_PROOF,
            ],
            'status_not_started' => KybStatusEnum::NOT_STARTED,
            'status_pending' => KybStatusEnum::PENDING,
            'status_under_review' => KybStatusEnum::UNDER_REVIEW,
            'status_approved' => KybStatusEnum::APPROVED,
            'status_rejected' => KybStatusEnum::REJECTED,
            'label' => 'KYB',
            'label_full' => 'Know Your Business',
        ];
    }

    /**
     * KYC configuration for lenders.
     */
    protected static function getKycConfig(): array
    {
        return [
            'profile_class' => LenderProfile::class,
            'profile_relation' => 'lenderProfile',
            'status_enum' => KycStatusEnum::class,
            'status_field' => 'kyc_status',
            'submitted_at_field' => 'kyc_submitted_at',
            'storage_path' => 'documents/kyc',
            'archive_type' => 'kyc',
            'allowed_document_types' => [
                DocumentTypeEnum::KYC_ID,
                DocumentTypeEnum::KYC_ADDRESS_PROOF,
                DocumentTypeEnum::KYC_ACCREDITATION,
            ],
            'required_document_types' => [
                DocumentTypeEnum::KYC_ID,
                DocumentTypeEnum::KYC_ADDRESS_PROOF,
                DocumentTypeEnum::KYC_ACCREDITATION,
            ],
            'status_not_started' => KycStatusEnum::NOT_STARTED,
            'status_pending' => KycStatusEnum::PENDING,
            'status_under_review' => KycStatusEnum::UNDER_REVIEW,
            'status_approved' => KycStatusEnum::APPROVED,
            'status_rejected' => KycStatusEnum::REJECTED,
            'label' => 'KYC',
            'label_full' => 'Know Your Customer',
        ];
    }

    /**
     * Get profile from user based on verification type.
     */
    public static function getProfile(object $user, string $type): ?object
    {
        $config = self::get($type);
        $relation = $config['profile_relation'];
        return $user->$relation;
    }

//    /**
//     * Get allowed document type values for validation.
//     */
//    public static function getAllowedDocumentTypeValues(string $type): array
//    {
//        $config = self::get($type);
//        return array_map(fn ($enum) => $enum->value, $config['allowed_document_types']);
//    }
//
//    /**
//     * Get required document type values.
//     */
//    public static function getRequiredDocumentTypeValues(string $type): array
//    {
//        $config = self::get($type);
//        return array_map(fn ($enum) => $enum->value, $config['required_document_types']);
//    }
}
