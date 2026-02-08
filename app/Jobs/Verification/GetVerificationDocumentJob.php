<?php

namespace App\Jobs\Verification;

use App\Config\VerificationConfig;
use App\Http\Resources\DocumentResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetVerificationDocumentJob
{
    protected User $user;
    protected int $documentId;
    protected string $verificationType;

    public function __construct(User $user, int $documentId, string $verificationType)
    {
        $this->user = $user;
        $this->documentId = $documentId;
        $this->verificationType = $verificationType;
    }

    public function handle(): JsonResponse
    {
        try {
            $config = VerificationConfig::get($this->verificationType);
            $profile = VerificationConfig::getProfile($this->user, $this->verificationType);
            $allowedTypes = $config['allowed_document_types'];

            $document = $profile->documents()
                ->whereIn('document_type', $allowedTypes)
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
