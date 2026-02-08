<?php

namespace App\Config;

use App\Enums\DocumentTypeEnum;
use App\Enums\KybStatusEnum;
use App\Models\DeveloperProfile;
use App\Models\LenderProfile;

class VerificationConfig
{
    public const TYPE_KYB = 'kyb';
    public const TYPE_LENDER_KYB = 'lender_kyb';

    /**
     * Get configuration for a verification type.
     */
    public static function get(string $type): array
    {
        return match ($type) {
            self::TYPE_KYB => self::getKybConfig(),
            self::TYPE_LENDER_KYB => self::getLenderKybConfig(),
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
     * KYB configuration for lenders.
     */
    protected static function getLenderKybConfig(): array
    {
        return [
            'profile_class' => LenderProfile::class,
            'profile_relation' => 'lenderProfile',
            'status_enum' => KybStatusEnum::class,
            'status_field' => 'kyb_status',
            'submitted_at_field' => 'kyb_submitted_at',
            'storage_path' => 'documents/lender_kyb',
            'archive_type' => 'lender_kyb',
            'allowed_document_types' => [
                DocumentTypeEnum::KYB_LENDER_CERTIFICATE_OF_INCORPORATION,
                DocumentTypeEnum::KYB_LENDER_BUSINESS_LICENSE,
                DocumentTypeEnum::KYB_LENDER_BENEFICIAL_OWNERSHIP,
                DocumentTypeEnum::KYB_LENDER_TAX_CERTIFICATE,
                DocumentTypeEnum::KYB_LENDER_ADDRESS_PROOF,
            ],
            'required_document_types' => [
                DocumentTypeEnum::KYB_LENDER_CERTIFICATE_OF_INCORPORATION,
                DocumentTypeEnum::KYB_LENDER_BUSINESS_LICENSE,
                DocumentTypeEnum::KYB_LENDER_BENEFICIAL_OWNERSHIP,
                DocumentTypeEnum::KYB_LENDER_TAX_CERTIFICATE,
                DocumentTypeEnum::KYB_LENDER_ADDRESS_PROOF,
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
