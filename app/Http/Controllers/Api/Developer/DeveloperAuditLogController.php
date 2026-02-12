<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Jobs\Developer\ListDeveloperAuditLogsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeveloperAuditLogController extends Controller
{
    /**
     * List all blockchain audit logs for the developer.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new ListDeveloperAuditLogsJob(
            user: $request->user(),
            perPage: min((int) $request->get('per_page', 15), 100)
        );

        return $job->handle();
    }
}
