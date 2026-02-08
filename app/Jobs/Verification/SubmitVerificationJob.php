<?php

namespace App\Jobs\Verification;

use App\Config\VerificationConfig;
use App\Enums\DocumentTypeEnum;
use App\Jobs\Documents\CreateDocumentArchiveJob;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class SubmitVerificationJob
{
    protected User $user;
    protected string $verificationType;

    public function __construct(User $user, string $verificationType)
    {
        $this->user = $user;
        $this->verificationType = $verificationType;
    }

    public function handle(): JsonResponse
    {
        try {
            $config = VerificationConfig::get($this->verificationType);
            $profile = VerificationConfig::getProfile($this->user, $this->verificationType);
            $statusField = $config['status_field'];
            $submittedAtField = $config['submitted_at_field'];
            $statusApproved = $config['status_approved'];
            $statusPending = $config['status_pending'];
            $statusUnderReview = $config['status_under_review'];
            $requiredTypes = $config['required_document_types'];
            $archiveType = $config['archive_type'];
            $label = $config['label'];

            // Check if already submitted or approved
            if ($profile->$statusField === $statusApproved) {
                return response()->json([
                    'success' => false,
                    'message' => "{$label} verification is already approved.",
                ], 400);
            }

            if (in_array($profile->$statusField, [$statusPending, $statusUnderReview])) {
                return response()->json([
                    'success' => false,
                    'message' => "{$label} verification is already submitted and pending review.",
                ], 400);
            }

            // Check if all required documents are uploaded
            $requiredTypeValues = array_map(fn ($enum) => $enum->value, $requiredTypes);

            $uploadedTypes = $profile->documents()
                ->whereIn('document_type', $requiredTypes)
                ->pluck('document_type')
                ->map(fn ($type) => $type->value)
                ->toArray();

            $missingTypes = array_diff($requiredTypeValues, $uploadedTypes);

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

            // Update status
            $profile->update([
                $statusField => $statusPending,
                $submittedAtField => now(),
            ]);

            // Dispatch job to create zip archive of documents
            CreateDocumentArchiveJob::dispatch($profile, $archiveType)
                ->delay(now()->addMinutes(2));

            return response()->json([
                'success' => true,
                'message' => "{$label} verification submitted successfully. Our team will review your documents.",
                'data' => [
                    "{$statusField}" => $profile->$statusField,
                    "{$submittedAtField}" => $profile->$submittedAtField,
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
