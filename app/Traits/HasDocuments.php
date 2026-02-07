<?php

namespace App\Traits;

use App\Enums\DocumentTypeEnum;
use App\Enums\VerificationStatusEnum;
use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasDocuments
{
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function verifiedDocuments(): MorphMany
    {
        return $this->documents()->where('verification_status', VerificationStatusEnum::VERIFIED);
    }

    public function pendingDocuments(): MorphMany
    {
        return $this->documents()->where('verification_status', VerificationStatusEnum::PENDING);
    }

    public function getDocumentsByType(DocumentTypeEnum $type): MorphMany
    {
        return $this->documents()->where('document_type', $type);
    }

    public function hasVerifiedDocument(DocumentTypeEnum $type): bool
    {
        return $this->documents()
            ->where('document_type', $type)
            ->where('verification_status', VerificationStatusEnum::VERIFIED)
            ->exists();
    }

    public function allRequiredDocumentsVerified(array $requiredTypes): bool
    {
        foreach ($requiredTypes as $type) {
            if (!$this->hasVerifiedDocument($type)) {
                return false;
            }
        }
        return true;
    }
}
