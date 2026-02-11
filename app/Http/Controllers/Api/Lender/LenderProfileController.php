<?php

namespace App\Http\Controllers\Api\Lender;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lender\UpdateLenderProfileRequest;
use App\Jobs\Lender\GetLenderProfileJob;
use App\Jobs\Lender\UpdateLenderProfileJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenderProfileController extends Controller
{
    /**
     * Get the current lender's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $job = new GetLenderProfileJob(
            user: $request->user()
        );

        return $job->handle();
    }

    /**
     * Update the lender's profile.
     */
    public function update(UpdateLenderProfileRequest $request): JsonResponse
    {
        $job = new UpdateLenderProfileJob(
            user: $request->user(),
            data: $request->validated()
        );

        return $job->handle();
    }
}
