<?php

namespace App\Jobs\Developer;

use App\Http\Resources\ProjectResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListProjectsJob
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
            $developerProfile = $this->user->developerProfile;

            $query = $developerProfile->projects()
                ->with(['featuredPhoto', 'photos'])
                ->withCount('documents')
                ->orderBy('created_at', 'desc');

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

            $projects = $query->paginate($this->perPage);

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
