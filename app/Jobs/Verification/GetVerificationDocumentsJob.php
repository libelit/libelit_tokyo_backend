<?php

namespace App\Jobs\Verification;

use App\Config\VerificationConfig;
use App\Http\Resources\DocumentResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class GetVerificationDocumentsJob
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
            $allowedTypes = $config['allowed_document_types'];

            $documents = $profile->documents()
                ->whereIn('document_type', $allowedTypes)
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
