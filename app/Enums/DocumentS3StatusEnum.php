<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentS3StatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case FILE_UPLOAD_TO_S3_PROCESSING = 'file_upload_to_s3_processing';
    case FILE_UPLOAD_TO_S3_SUCCESSFUL = 'file_upload_to_s3_successful';
    case FILE_UPLOAD_TO_S3_FAILED = 'file_upload_to_s3_failed';
    case ZIP_FILE_CREATION_PROCESSING = 'zip_file_creation_processing';
    case ZIP_FILE_CREATION_SUCCESS = 'zip_file_creation_success';
    case ZIP_FILE_CREATION_FAILED = 'zip_file_creation_failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::FILE_UPLOAD_TO_S3_PROCESSING => 'Uploading to S3',
            self::FILE_UPLOAD_TO_S3_SUCCESSFUL => 'Upload Successful',
            self::FILE_UPLOAD_TO_S3_FAILED => 'Upload Failed',
            self::ZIP_FILE_CREATION_PROCESSING => 'Creating Archive',
            self::ZIP_FILE_CREATION_SUCCESS => 'Archive Created',
            self::ZIP_FILE_CREATION_FAILED => 'Archive Failed',
        };
    }

    public function isProcessing(): bool
    {
        return in_array($this, [
            self::FILE_UPLOAD_TO_S3_PROCESSING,
            self::ZIP_FILE_CREATION_PROCESSING,
        ]);
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [
            self::FILE_UPLOAD_TO_S3_SUCCESSFUL,
            self::ZIP_FILE_CREATION_SUCCESS,
        ]);
    }

    public function isFailed(): bool
    {
        return in_array($this, [
            self::FILE_UPLOAD_TO_S3_FAILED,
            self::ZIP_FILE_CREATION_FAILED,
        ]);
    }
}
