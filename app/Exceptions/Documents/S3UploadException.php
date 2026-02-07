<?php

namespace App\Exceptions\Documents;

use Exception;

class S3UploadException extends Exception
{
    public function __construct(string $filePath, string $message = null)
    {
        $message = $message ?? "Failed to upload file to S3: {$filePath}";
        parent::__construct($message);
    }
}
