<?php

namespace App\Jobs\Developer;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\DocumentS3StatusEnum;
use App\Enums\MilestoneStatusEnum;
use App\Http\Resources\ProjectMilestoneResource;
use App\Managers\AuditTrailManager;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompleteMilestoneJob
{
    protected User $user;
    protected int $projectId;
    protected int $milestoneId;
    protected array $proofs;

    public function __construct(User $user, int $projectId, int $milestoneId, array $proofs)
    {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestoneId = $milestoneId;
        $this->proofs = $proofs;
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

            if (!$milestone->canComplete()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This milestone cannot be completed. Only pending or rejected milestones can be completed.',
                ], 403);
            }

            DB::transaction(function () use ($milestone) {
                // Upload all proofs
                foreach ($this->proofs as $proofData) {
                    $proofType = $proofData['proof_type'];
                    $title = $proofData['title'];
                    $description = $proofData['description'] ?? null;
                    $file = $proofData['file'];

                    // Store file locally
                    $uuid = Str::uuid()->toString();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = $proofType . '_' . time() . '.' . $extension;
                    $filePath = $file->storeAs(
                        'proofs/milestone/' . $milestone->id . '/' . $uuid,
                        $fileName,
                        'public'
                    );

                    $milestone->proofs()->create([
                        'proof_type' => $proofType,
                        'title' => $title,
                        'description' => $description,
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'storage_disk' => 'local',
                        's3_status' => DocumentS3StatusEnum::PENDING,
                        'uploaded_by' => $this->user->id,
                    ]);
                }

                // Update milestone status to proof submitted
                $milestone->update([
                    'status' => MilestoneStatusEnum::PROOF_SUBMITTED,
                    'proof_submitted_at' => now(),
                ]);
            });

            $milestone->refresh();
            $milestone->loadCount('proofs');

            // Record blockchain audit trail for milestone proof submission
            AuditTrailManager::record(
                BlockchainAuditEventTypeEnum::MILESTONE_PAYMENT_REQUESTED,
                $milestone
            );

            return response()->json([
                'success' => true,
                'message' => 'Milestone completed and submitted for review successfully.',
                'data' => new ProjectMilestoneResource($milestone),
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
