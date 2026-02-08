<?php

namespace App\Http\Controllers\Api\Lender;

use App\Config\VerificationConfig;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lender\StoreKybDocumentRequest;
use App\Jobs\Verification\DeleteVerificationDocumentJob;
use App\Jobs\Verification\GetVerificationDocumentJob;
use App\Jobs\Verification\GetVerificationDocumentsJob;
use App\Jobs\Verification\StoreVerificationDocumentJob;
use App\Jobs\Verification\SubmitVerificationJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenderKybController extends Controller
{
    protected string $verificationType = VerificationConfig::TYPE_KYC;

    /**
     * List all KYC documents for the lender.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new GetVerificationDocumentsJob(
            user: $request->user(),
            verificationType: $this->verificationType
        );

        return $job->handle();
    }

    /**
     * Get a single KYC document.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = new GetVerificationDocumentJob(
            user: $request->user(),
            documentId: $id,
            verificationType: $this->verificationType
        );

        return $job->handle();
    }

    /**
     * Upload KYC documents.
     */
    public function store(StoreKybDocumentRequest $request): JsonResponse
    {
        $job = new StoreVerificationDocumentJob(
            user: $request->user(),
            documents: $request->validated('documents'),
            verificationType: $this->verificationType
        );

        return $job->handle();
    }

    /**
     * Delete a KYC document.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $job = new DeleteVerificationDocumentJob(
            user: $request->user(),
            documentId: $id,
            verificationType: $this->verificationType
        );

        return $job->handle();
    }

    /**
     * Submit KYC for review.
     */
    public function submit(Request $request): JsonResponse
    {
        $job = new SubmitVerificationJob(
            user: $request->user(),
            verificationType: $this->verificationType
        );

        return $job->handle();
    }
}
