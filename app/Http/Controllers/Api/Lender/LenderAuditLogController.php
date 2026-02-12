<?php

namespace App\Http\Controllers\Api\Lender;

use App\Http\Controllers\Controller;
use App\Jobs\Lender\ListLenderAuditLogsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LenderAuditLogController extends Controller
{
    /**
     * List all blockchain audit logs for the lender.
     */
    public function index(Request $request): JsonResponse
    {
        $job = new ListLenderAuditLogsJob(
            user: $request->user(),
            perPage: min((int) $request->get('per_page', 15), 100)
        );

        return $job->handle();
    }
}
