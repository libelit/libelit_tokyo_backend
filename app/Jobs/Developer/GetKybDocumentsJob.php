<?php

namespace App\Jobs\Developer;

use App\Enums\DocumentTypeEnum;
use App\Http\Resources\DocumentResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetKybDocumentsJob
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

            $documents = $developerProfile->documents()
                ->whereIn('document_type', [
                    DocumentTypeEnum::KYB_CERTIFICATE,
                    DocumentTypeEnum::KYB_ID,
                    DocumentTypeEnum::KYB_ADDRESS_PROOF,
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => DocumentResource::collection($documents),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
