<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\StoreKybDocumentRequest;
use App\Jobs\Developer\DeleteKybDocumentJob;
use App\Jobs\Developer\GetKybDocumentJob;
use App\Jobs\Developer\GetKybDocumentsJob;
use App\Jobs\Developer\StoreKybDocumentJob;
use App\Jobs\Developer\SubmitKybJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperKybController extends Controller
{
    /**
     * List all KYB documents for the developer.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new GetKybDocumentsJob(
            user: $request->user()
        );

        return $job->handle();
    }

    /**
     * Get a single KYB document.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = new GetKybDocumentJob(
            user: $request->user(),
            documentId: $id
        );

        return $job->handle();
    }

    /**
     * Upload KYB documents.
     */
    public function store(StoreKybDocumentRequest $request): JsonResponse
    {
        $job = new StoreKybDocumentJob(
            user: $request->user(),
            documents: $request->validated('documents')
        );

        return $job->handle();
    }

    /**
     * Delete a KYB document.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $job = new DeleteKybDocumentJob(
            user: $request->user(),
            documentId: $id
        );

        return $job->handle();
    }

    /**
     * Submit KYB for review.
     */
    public function submit(Request $request): JsonResponse
    {
        $job = new SubmitKybJob(
            user: $request->user()
        );

        return $job->handle();
    }
}
