<?php

namespace App\Jobs\Lender;

use App\Enums\DocumentS3StatusEnum;
use App\Enums\LoanProposalStatusEnum;
use App\Enums\MilestoneProofTypeEnum;
use App\Enums\MilestoneStatusEnum;
use App\Http\Resources\ProjectMilestoneResource;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadPaymentProofJob
{
    protected User $user;
    protected int $projectId;
    protected int $milestoneId;
    protected array $proofs;
    protected ?string $paymentReference;

    public function __construct(
        User $user,
        int $projectId,
        int $milestoneId,
        array $proofs,
        ?string $paymentReference = null
    ) {
        $this->user = $user;
        $this->projectId = $projectId;
        $this->milestoneId = $milestoneId;
        $this->proofs = $proofs;
        $this->paymentReference = $paymentReference;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            // Check if lender has access to this project
            $project = Project::where('id', $this->projectId)
                ->where(function ($query) use ($lenderProfile) {
                    $query->where('lender_id', $lenderProfile->id)
                        ->orWhereHas('loanProposals', function ($q) use ($lenderProfile) {
                            $q->where('lender_id', $lenderProfile->id)
                                ->whereIn('status', [
                                    LoanProposalStatusEnum::LOAN_PROPOSAL_ACCEPTED_BY_DEVELOPER,
                                    LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_DEVELOPER,
                                    LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_SIGNED_BY_LENDER,
                                    LoanProposalStatusEnum::LOAN_TERM_AGREEMENT_FULLY_EXECUTED,
                                ]);
                        });
                })
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or you do not have access to this project.',
                ], 404);
            }

            $milestone = $project->milestones()->find($this->milestoneId);

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found.',
                ], 404);
            }

            // Check if milestone is approved (can receive payment proof)
            if ($milestone->status !== MilestoneStatusEnum::APPROVED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof can only be uploaded for approved milestones. Current status: ' . $milestone->status->getLabel(),
                ], 422);
            }

            DB::transaction(function () use ($milestone) {
                // Upload all payment proofs
                foreach ($this->proofs as $proofData) {
                    $title = $proofData['title'];
                    $file = $proofData['file'];

                    // Store file locally
                    $uuid = Str::uuid()->toString();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'payment_proof_' . time() . '.' . $extension;
                    $filePath = $file->storeAs(
                        'proofs/payment/' . $milestone->id . '/' . $uuid,
                        $fileName,
                        'public'
                    );

                    $milestone->proofs()->create([
                        'proof_type' => MilestoneProofTypeEnum::BANK_STATEMENT,
                        'title' => $title,
                        'description' => 'Payment proof uploaded by lender',
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'storage_disk' => 'local',
                        's3_status' => DocumentS3StatusEnum::PENDING,
                        'uploaded_by' => $this->user->id,
                        'is_payment_proof' => true,
                        'payment_uploaded_by' => $this->user->id,
                    ]);
                }

                // Update milestone to PAID status
                $milestone->update([
                    'status' => MilestoneStatusEnum::PAID,
                    'paid_at' => now(),
                    'payment_reference' => $this->paymentReference,
                ]);
            });

            $milestone->refresh();
            $milestone->load('proofs');
            $milestone->loadCount('proofs');

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully. Milestone marked as paid.',
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
