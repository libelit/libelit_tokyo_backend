<?php

namespace App\Exceptions\Documents;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct(string $filePath, string $message = null)
    {
        $message = $message ?? "File not found: {$filePath}";
        parent::__construct($message);
    }
}
