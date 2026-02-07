<?php

namespace App\Jobs\Developer;

use App\Enums\DocumentTypeEnum;
use App\Http\Resources\DocumentResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetKybDocumentJob
{
    protected User $user;
    protected int $documentId;

    public function __construct(User $user, int $documentId)
    {
        $this->user = $user;
        $this->documentId = $documentId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $document = $developerProfile->documents()
                ->whereIn('document_type', [
                    DocumentTypeEnum::KYB_CERTIFICATE,
                    DocumentTypeEnum::KYB_ID,
                    DocumentTypeEnum::KYB_ADDRESS_PROOF,
                ])
                ->find($this->documentId);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new DocumentResource($document),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
