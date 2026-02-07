<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Developer\UpdateDeveloperProfileRequest;
use App\Jobs\Developer\GetProfileJob;
use App\Jobs\Developer\UpdateProfileJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperProfileController extends Controller
{
    /**
     * Get the current developer's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $job = new GetProfileJob(
            user: $request->user()
        );

        return $job->handle();
    }

    /**
     * Update the developer's profile.
     */
    public function update(UpdateDeveloperProfileRequest $request): JsonResponse
    {
        $job = new UpdateProfileJob(
            user: $request->user(),
            data: $request->validated()
        );

        return $job->handle();
    }
}
