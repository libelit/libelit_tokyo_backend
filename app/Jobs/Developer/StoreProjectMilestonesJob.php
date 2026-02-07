<?php

namespace App\Jobs\Developer;

use App\Enums\MilestoneStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Http\Resources\ProjectMilestoneResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StoreProjectMilestonesJob
{
    protected User $user;
    protected int $projectId;
    protected array $milestones;

    public function __construct(User $user, int $projectId, array $milestones)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestones = $milestones;
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

            // Only allow creating/updating milestones for draft or rejected projects
            if (!in_array($project->status, [ProjectStatusEnum::DRAFT, ProjectStatusEnum::REJECTED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestones can only be created for draft or rejected projects.',
                ], 403);
            }

            // Validate total amount equals funding goal
            $totalAmount = collect($this->milestones)->sum('amount');
            $fundingGoal = (float) $project->funding_goal;

            if (abs($totalAmount - $fundingGoal) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total milestone amounts must equal the project funding goal.',
                    'data' => [
                        'total_amount' => $totalAmount,
                        'funding_goal' => $fundingGoal,
                        'difference' => $fundingGoal - $totalAmount,
                    ],
                ], 422);
            }

            DB::beginTransaction();

            // Delete existing milestones (only pending ones can be deleted)
            $existingMilestones = $project->milestones()->get();

            foreach ($existingMilestones as $milestone) {
                if ($milestone->status !== MilestoneStatusEnum::PENDING) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot modify milestones that have already been started.',
                    ], 403);
                }
            }

            // Soft delete all existing milestones
            $project->milestones()->delete();

            // Create new milestones
            $createdMilestones = [];
            foreach ($this->milestones as $index => $milestoneData) {
                $percentage = $fundingGoal > 0
                    ? round(($milestoneData['amount'] / $fundingGoal) * 100, 2)
                    : 0;

                $milestone = $project->milestones()->create([
                    'title' => $milestoneData['title'],
                    'description' => $milestoneData['description'] ?? null,
                    'sequence' => $index + 1,
                    'amount' => $milestoneData['amount'],
                    'percentage' => $percentage,
                    'status' => MilestoneStatusEnum::PENDING,
                    'due_date' => $milestoneData['due_date'] ?? null,
                ]);

                $createdMilestones[] = $milestone;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Milestones saved successfully.',
                'data' => [
                    'milestones' => ProjectMilestoneResource::collection($createdMilestones),
                    'count' => count($createdMilestones),
                ],
            ], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
