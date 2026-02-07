<?php

namespace App\Exceptions\Documents;

use Exception;

class S3DownloadException extends Exception
{
    public function __construct(string $s3Path, string $message = null)
    {
        $message = $message ?? "Failed to download file from S3: {$s3Path}";
        parent::__construct($message);
    }
}
