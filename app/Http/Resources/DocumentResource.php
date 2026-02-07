<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'document_type_label' => $this->document_type?->getLabel(),
            'title' => $this->title,
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'file_size_formatted' => $this->formatFileSize($this->file_size),
            'mime_type' => $this->mime_type,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'rejection_reason' => $this->rejection_reason,
            'expires_at' => $this->expires_at,
            'is_public' => $this->is_public,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'file_url' => $this->file_url, // Uses model accessor (handles both local & S3)
        ];
    }

    /**
     * Format file size to human readable format.
     */
    private function formatFileSize(?int $bytes): ?string
    {
        if (!$bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
