<?php

namespace App\Jobs\Lender;

use App\Enums\KybStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListLenderProjectsJob
{
    protected User $user;
    protected ?string $status;
    protected ?string $search;
    protected int $perPage;

    public function __construct(User $user, ?string $status = null, ?string $search = null, int $perPage = 15)
    {
        $this->user = $user;
        $this->status = $status;
        $this->search = $search;
        $this->perPage = $perPage;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            // Check KYB status
            if ($lenderProfile->kyb_status !== KybStatusEnum::APPROVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete KYB verification before viewing projects.',
                    'kyb_status' => $lenderProfile->kyb_status,
                ], 403);
            }

            // Get invested project IDs
            $investedProjectIds = $lenderProfile->investments()->pluck('project_id')->toArray();

            // Build query - show approved/funded/completed projects + invested projects
            $query = Project::query()
                ->with(['developer.user', 'token'])
                ->withCount('milestones');

            $publicStatuses = [
                ProjectStatusEnum::APPROVED,
                ProjectStatusEnum::FUNDING,
                ProjectStatusEnum::FUNDED,
                ProjectStatusEnum::COMPLETED,
            ];

            $query->where(function ($q) use ($publicStatuses, $investedProjectIds) {
                $q->whereIn('status', $publicStatuses);

                if (!empty($investedProjectIds)) {
                    $q->orWhereIn('id', $investedProjectIds);
                }
            });

            // Filter by status if provided
            if ($this->status && $this->status !== 'all') {
                $query->where('status', $this->status);
            }

            // Search by title or location
            if ($this->search) {
                $search = $this->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            }

            $projects = $query->latest()->paginate($this->perPage);

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
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
