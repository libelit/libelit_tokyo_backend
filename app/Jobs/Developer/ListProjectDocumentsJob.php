<?php

namespace App\Jobs\Developer;

use App\Enums\DocumentTypeEnum;
use App\Http\Resources\DocumentResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListProjectDocumentsJob
{
    protected User $user;
    protected int $projectId;

    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            $documents = $project->documents()
                ->whereIn('document_type', [
                    DocumentTypeEnum::LOAN_DRAWINGS,
                    DocumentTypeEnum::LOAN_COST_CALCULATION,
                    DocumentTypeEnum::LOAN_PHOTOS,
                    DocumentTypeEnum::LOAN_LAND_TITLE,
                    DocumentTypeEnum::LOAN_BANK_STATEMENT,
                    DocumentTypeEnum::LOAN_REVENUE_EVIDENCE,
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Get required document types and their status
            $requiredTypes = [
                DocumentTypeEnum::LOAN_DRAWINGS->value,
                DocumentTypeEnum::LOAN_COST_CALCULATION->value,
                DocumentTypeEnum::LOAN_PHOTOS->value,
                DocumentTypeEnum::LOAN_LAND_TITLE->value,
                DocumentTypeEnum::LOAN_BANK_STATEMENT->value,
            ];

            $uploadedTypes = $documents->pluck('document_type')
                ->map(fn ($type) => $type->value)
                ->toArray();

            $documentStatus = [];
            foreach ($requiredTypes as $type) {
                $enum = DocumentTypeEnum::from($type);
                $documentStatus[] = [
                    'type' => $type,
                    'label' => $enum->getLabel(),
                    'required' => true,
                    'uploaded' => in_array($type, $uploadedTypes),
                ];
            }

            // Add optional document
            $documentStatus[] = [
                'type' => DocumentTypeEnum::LOAN_REVENUE_EVIDENCE->value,
                'label' => DocumentTypeEnum::LOAN_REVENUE_EVIDENCE->getLabel(),
                'required' => false,
                'uploaded' => in_array(DocumentTypeEnum::LOAN_REVENUE_EVIDENCE->value, $uploadedTypes),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'documents' => DocumentResource::collection($documents),
                    'document_checklist' => $documentStatus,
                    'all_required_uploaded' => count(array_diff($requiredTypes, $uploadedTypes)) === 0,
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
