<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\StoreProjectRequest;
use App\Http\Requests\Developer\UpdateProjectRequest;
use App\Jobs\Developer\CreateProjectJob;
use App\Jobs\Developer\DeleteProjectJob;
use App\Jobs\Developer\GetProjectJob;
use App\Jobs\Developer\ListProjectsJob;
use App\Jobs\Developer\SubmitProjectJob;
use App\Jobs\Developer\UpdateProjectJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperProjectController extends Controller
{
    /**
     * List all projects for the developer.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new ListProjectsJob(
            user: $request->user(),
            status: $request->get('status'),
            search: $request->get('search'),
            perPage: $request->get('per_page', 15)
        );

        return $job->handle();
    }

    /**
     * Create a new project.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $job = new CreateProjectJob(
            user: $request->user(),
            data: $request->validated()
        );

        return $job->handle();
    }

    /**
     * Get a single project.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = new GetProjectJob(
            user: $request->user(),
            projectId: $id
        );

        return $job->handle();
    }

    /**
     * Update a project.
     */
    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        $job = new UpdateProjectJob(
            user: $request->user(),
            projectId: $id,
            data: $request->validated()
        );

        return $job->handle();
    }

    /**
     * Delete a project.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $job = new DeleteProjectJob(
            user: $request->user(),
            projectId: $id
        );

        return $job->handle();
    }

    /**
     * Submit a project for review.
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $job = new SubmitProjectJob(
            user: $request->user(),
            projectId: $id
        );

        return $job->handle();
    }
}
