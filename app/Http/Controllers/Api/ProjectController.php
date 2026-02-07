<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectStatusEnum;
use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * List projects based on user type.
     * - Investors: see approved/funded projects + projects they've invested in
     * - Developers: see only their own projects
     * - Admins: see all projects
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Project::query()
                ->with(['developer.user', 'token'])
                ->withCount('milestones');

            // Apply filters based on user type
            if ($user->type === UserTypeEnum::ADMIN) {
                // No filter - admin sees all projects
            } elseif ($user->type === UserTypeEnum::DEVELOPER) {
                $this->filterForDeveloper($query, $user);
            } elseif ($user->type === UserTypeEnum::INVESTOR) {
                $this->filterForInvestor($query, $user);
            } else {
                // Unknown user type - show only approved/funded projects
                $this->filterForInvestor($query, $user);
            }

            $projects = $query->latest()->paginate(15);

            return response()->json([
                'success' => true,
                'data' => ProjectResource::collection($projects),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch projects: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Filter projects for developer - only their own projects.
     */
    protected function filterForDeveloper($query, $user): void
    {
        $developerProfile = $user->developerProfile;

        if ($developerProfile) {
            $query->where('developer_id', $developerProfile->id);
        } else {
            // Developer has no profile, return empty
            $query->whereRaw('1 = 0');
        }
    }

    /**
     * Filter projects for investor - approved/funded projects + invested projects.
     */
    protected function filterForInvestor($query, $user): void
    {
        $investorProfile = $user->investorProfile;
        $investedProjectIds = [];

        if ($investorProfile) {
            $investedProjectIds = $investorProfile->investments()->pluck('project_id')->toArray();
        }

        // Show approved projects (available for investment) and funded projects
        $publicStatuses = [
            ProjectStatusEnum::APPROVED,
            ProjectStatusEnum::FUNDED,
            ProjectStatusEnum::COMPLETED,
        ];

        $query->where(function ($q) use ($publicStatuses, $investedProjectIds) {
            $q->whereIn('status', $publicStatuses);

            if (!empty($investedProjectIds)) {
                $q->orWhereIn('id', $investedProjectIds);
            }
        });
    }
}
