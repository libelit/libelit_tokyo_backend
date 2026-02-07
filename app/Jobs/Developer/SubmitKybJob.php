<?php

namespace App\Jobs\Developer;

use App\Enums\DocumentTypeEnum;
use App\Enums\KybStatusEnum;
use App\Jobs\Documents\CreateDocumentArchiveJob;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class SubmitKybJob
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            // Check if already submitted or approved
            if ($developerProfile->kyb_status === KybStatusEnum::APPROVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYB verification is already approved.',
                ], 400);
            }

            if (in_array($developerProfile->kyb_status, [KybStatusEnum::PENDING, KybStatusEnum::UNDER_REVIEW])) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYB verification is already submitted and pending review.',
                ], 400);
            }

            // Check if all required documents are uploaded
            $requiredTypes = [
                DocumentTypeEnum::KYB_CERTIFICATE->value,
                DocumentTypeEnum::KYB_ID->value,
                DocumentTypeEnum::KYB_ADDRESS_PROOF->value,
            ];

            $uploadedTypes = $developerProfile->documents()
                ->whereIn('document_type', $requiredTypes)
                ->pluck('document_type')
                ->map(fn ($type) => $type->value)
                ->toArray();

            $missingTypes = array_diff($requiredTypes, $uploadedTypes);

            if (!empty($missingTypes)) {
                $missingDocuments = array_map(function ($type) {
                    $enum = DocumentTypeEnum::from($type);
                    return [
                        'type' => $type,
                        'label' => $enum->getLabel(),
                    ];
                }, $missingTypes);

                return response()->json([
                    'success' => false,
                    'message' => 'Please upload all required documents before submitting.',
                    'missing_documents' => array_values($missingDocuments),
                ], 422);
            }

            // Update KYB status
            $developerProfile->update([
                'kyb_status' => KybStatusEnum::PENDING,
                'kyb_submitted_at' => now(),
            ]);

            // Dispatch job to create zip archive of KYB documents
            CreateDocumentArchiveJob::dispatch($developerProfile, 'kyb')
                ->delay(now()->addMinutes(2));

            return response()->json([
                'success' => true,
                'message' => 'KYB verification submitted successfully. Our team will review your documents.',
                'data' => [
                    'kyb_status' => $developerProfile->kyb_status,
                    'kyb_submitted_at' => $developerProfile->kyb_submitted_at,
                ],
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
