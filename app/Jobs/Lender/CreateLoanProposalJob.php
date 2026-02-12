<?php

namespace App\Jobs\Lender;

use App\Enums\BlockchainAuditEventTypeEnum;
use App\Enums\DocumentS3StatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\LoanProposalStatusEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\VerificationStatusEnum;
use App\Http\Resources\LoanProposalResource;
use App\Jobs\Documents\UploadDocumentToS3Job;
use App\Managers\AuditTrailManager;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLoanProposalJob
{
    protected User $user;
    protected array $data;

    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    public function handle(): JsonResponse
    {
        try {
            $lenderProfile = $this->user->lenderProfile;

            // Find the project by ID
            $project = Project::find($this->data['project_id']);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found.',
                ], 404);
            }

            // Check if project is in LISTED state
            if ($project->status !== ProjectStatusEnum::LISTED) {
                return response()->json([
                    'success' => false,
                    'message' => 'This project is not accepting loan proposals.',
                ], 403);
            }

            // Check if lender already has an active proposal for this project
            $existingProposal = $lenderProfile->loanProposals()
                ->where('project_id', $project->id)
                ->whereNotIn('status', [
                    LoanProposalStatusEnum::LOAN_PROPOSAL_REJECTED_BY_DEVELOPER,
                    LoanProposalStatusEnum::LOAN_PROPOSAL_EXPIRED,
                ])
                ->first();

            if ($existingProposal) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active proposal for this project.',
                ], 403);
            }

            $loanProposal = null;

            DB::transaction(function () use ($lenderProfile, $project, &$loanProposal) {
                // Create the loan proposal
                $loanProposal = $lenderProfile->loanProposals()->create([
                    'uuid' => Str::uuid(),
                    'project_id' => $project->id,
                    'loan_amount_offered' => $this->data['loan_amount_offered'],
                    'currency' => $this->data['currency'],
                    'interest_rate' => $this->data['interest_rate'],
                    'loan_maturity_date' => $this->data['loan_maturity_date'],
                    'security_packages' => $this->data['security_packages'],
                    'max_ltv_accepted' => $this->data['max_ltv_accepted'] ?? null,
                    'bid_expiry_date' => $this->data['bid_expiry_date'],
                    'additional_conditions' => $this->data['additional_conditions'] ?? null,
                    'status' => LoanProposalStatusEnum::LOAN_PROPOSAL_SUBMITTED_BY_LENDER,
                ]);

                // Handle loan term agreement document upload
                if (isset($this->data['loan_term_agreement'])) {
                    $file = $this->data['loan_term_agreement'];
                    $uuid = Str::uuid()->toString();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'loan_term_agreement.' . $extension;
                    $filePath = $file->storeAs('documents/loan-proposal/' . $uuid, $fileName, 'public');

                    $document = $loanProposal->documents()->create([
                        'uuid' => $uuid,
                        'document_type' => DocumentTypeEnum::LOAN_TERM_AGREEMENT,
                        'title' => 'Loan Term Agreement',
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'uploaded_by' => $this->user->id,
                        'verification_status' => VerificationStatusEnum::PENDING,
                        'storage_disk' => 'local',
                        's3_status' => DocumentS3StatusEnum::PENDING,
                    ]);

                    // Dispatch async job to upload to S3
                    UploadDocumentToS3Job::dispatch($document)->delay(now()->addSeconds(5));
                }
            });

            $loanProposal->load(['project', 'lender', 'documents']);

            // Record blockchain audit trail
            AuditTrailManager::record(
                BlockchainAuditEventTypeEnum::LOAN_PROPOSAL_SUBMITTED,
                $loanProposal
            );

            return response()->json([
                'success' => true,
                'message' => 'Loan proposal submitted successfully.',
                'data' => new LoanProposalResource($loanProposal),
            ], 201);
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
