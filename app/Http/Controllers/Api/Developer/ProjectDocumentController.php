<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\StoreProjectDocumentRequest;
use App\Jobs\Developer\DeleteProjectDocumentJob;
use App\Jobs\Developer\ListProjectDocumentsJob;
use App\Jobs\Developer\StoreProjectDocumentJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectDocumentController extends Controller
{
    /**
     * List all documents for a project.
     */
    public function index(Request $request, int $projectId): JsonResponse
    {
        $job = new ListProjectDocumentsJob(
            user: $request->user(),
            projectId: $projectId
        );

        return $job->handle();
    }

    /**
     * Upload documents to a project.
     */
    public function store(StoreProjectDocumentRequest $request, int $projectId): JsonResponse
    {
        $job = new StoreProjectDocumentJob(
            user: $request->user(),
            projectId: $projectId,
            documents: $request->validated('documents')
        );

        return $job->handle();
    }

    /**
     * Delete a document from a project.
     */
    public function destroy(Request $request, int $projectId, int $id): JsonResponse
    {
        $job = new DeleteProjectDocumentJob(
            user: $request->user(),
            projectId: $projectId,
            documentId: $id
        );

        return $job->handle();
    }
}
