<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public int $status;
    public int $errorCode;

    public function __construct(string $message, int $errorCode = 3, int $status = 400)
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errorCode = $errorCode;
    }
}
