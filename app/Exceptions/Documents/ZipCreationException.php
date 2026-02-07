<?php

namespace App\Exceptions\Documents;

use Exception;

class ZipCreationException extends Exception
{
    public function __construct(string $zipPath, string $message = null)
    {
        $message = $message ?? "Failed to create zip file: {$zipPath}";
        parent::__construct($message);
    }
}
