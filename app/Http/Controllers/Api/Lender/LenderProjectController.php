<?php

namespace App\Http\Controllers\Api\Lender;

use App\Http\Controllers\Controller;
use App\Jobs\Lender\GetLenderProjectJob;
use App\Jobs\Lender\ListLenderProjectsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenderProjectController extends Controller
{
    /**
     * List projects available for the lender.
     * Requires KYC approval.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new ListLenderProjectsJob(
            user: $request->user(),
            status: $request->get('status'),
            search: $request->get('search'),
            perPage: $request->get('per_page', 15)
        );

        return $job->handle();
    }

    /**
     * Get a single project.
     * Requires KYC approval.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $job = new GetLenderProjectJob(
            user: $request->user(),
            projectId: $id
        );

        return $job->handle();
    }
}
