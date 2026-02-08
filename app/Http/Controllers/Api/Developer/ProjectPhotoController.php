<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\StoreProjectPhotoRequest;
use App\Http\Requests\Developer\UpdateProjectPhotoRequest;
use App\Jobs\Developer\DeleteProjectPhotoJob;
use App\Jobs\Developer\ListProjectPhotosJob;
use App\Jobs\Developer\StoreProjectPhotoJob;
use App\Jobs\Developer\UpdateProjectPhotoJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPhotoController extends Controller
{
    /**
     * List all photos for a project.
     */
    public function index(Request $request, int $projectId): JsonResponse
    {
        $job = new ListProjectPhotosJob(
            user: $request->user(),
            projectId: $projectId
        );

        return $job->handle();
    }

    /**
     * Upload photos to a project.
     */
    public function store(StoreProjectPhotoRequest $request, int $projectId): JsonResponse
    {
        $job = new StoreProjectPhotoJob(
            user: $request->user(),
            projectId: $projectId,
            photos: $request->validated('photos')
        );

        return $job->handle();
    }

    /**
     * Update a photo (caption, featured status, sort order).
     */
    public function update(UpdateProjectPhotoRequest $request, int $projectId, int $photoId): JsonResponse
    {
        $job = new UpdateProjectPhotoJob(
            user: $request->user(),
            projectId: $projectId,
            photoId: $photoId,
            data: $request->validated()
        );

        return $job->handle();
    }

    /**
     * Delete a photo.
     */
    public function destroy(Request $request, int $projectId, int $photoId): JsonResponse
    {
        $job = new DeleteProjectPhotoJob(
            user: $request->user(),
            projectId: $projectId,
            photoId: $photoId
        );

        return $job->handle();
    }
}
