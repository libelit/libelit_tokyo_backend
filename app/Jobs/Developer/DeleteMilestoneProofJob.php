<?php

namespace App\Jobs\Developer;

use App\Enums\MilestoneStatusEnum;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DeleteMilestoneProofJob
{
    protected User $user;
    protected int $projectId;
    protected int $milestoneId;
    protected int $proofId;

    public function __construct(User $user, int $projectId, int $milestoneId, int $proofId)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestoneId = $milestoneId;
        $this->proofId = $proofId;
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

            $milestone = $project->milestones()->find($this->milestoneId);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found.',
                ], 404);
            }

            // Only allow deleting proofs for in-progress or rejected milestones
            if (!in_array($milestone->status, [
                MilestoneStatusEnum::IN_PROGRESS,
                MilestoneStatusEnum::REJECTED,
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proofs can only be deleted for in-progress or rejected milestones.',
                ], 403);
            }

            $proof = $milestone->proofs()->find($this->proofId);

            if (!$proof) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proof not found.',
                ], 404);
            }

            // Delete file from storage
            if ($proof->file_path && Storage::disk('public')->exists($proof->file_path)) {
                Storage::disk('public')->delete($proof->file_path);
            }

            // Delete from S3 if exists
            if ($proof->s3_path && $proof->storage_disk === 's3') {
                try {
                    Storage::disk('s3')->delete($proof->s3_path);
                } catch (Exception $e) {
                    \Log::warning('Failed to delete proof from S3', [
                        'proof_id' => $proof->id,
                        's3_path' => $proof->s3_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $proof->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proof deleted successfully.',
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
