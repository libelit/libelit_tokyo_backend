<?php

namespace App\Exceptions\Documents;

use Exception;

class S3VerificationException extends Exception
{
    public function __construct(string $s3Path, string $message = null)
    {
        $message = $message ?? "File verification failed on S3: {$s3Path}";
        parent::__construct($message);
    }
}
