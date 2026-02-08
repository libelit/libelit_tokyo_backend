<?php

namespace App\Jobs\Developer;

use App\Http\Resources\ProjectMilestoneResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;

class ListProjectMilestonesJob
{
    protected User $user;
    protected int $projectId;

    public function __construct(User $user, int $projectId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
    }

    public function handle(): JsonResponse
    {
        try {
            $developerProfile = $this->user->developerProfile;

            $project = $developerProfile->projects()->find($this->projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            $milestones = $project->milestones()
                ->withCount('proofs')
                ->orderBy('sequence')
                ->get();

            // Calculate milestone statistics
            $totalMilestones = $milestones->count();
            $completedMilestones = $milestones->whereIn('status', ['approved', 'paid'])->count();
            $totalAmount = $milestones->sum('amount');
            $paidAmount = $milestones->where('status', 'paid')->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'milestones' => ProjectMilestoneResource::collection($milestones),
                    'statistics' => [
                        'total_milestones' => $totalMilestones,
                        'completed_milestones' => $completedMilestones,
                        'progress_percentage' => $totalMilestones > 0
                            ? round(($completedMilestones / $totalMilestones) * 100, 2)
                            : 0,
                        'total_amount' => (float) $totalAmount,
                        'paid_amount' => (float) $paidAmount,
                        'loan_amount' => (float) $project->loan_amount,
                        'allocation_complete' => abs($totalAmount - $project->loan_amount) < 0.01,
                    ],
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
