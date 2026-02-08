<?php

namespace App\Http\Controllers\Api\Lender;

use App\Http\Controllers\Controller;
use App\Jobs\Lender\GetLenderProfileJob;
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
}
