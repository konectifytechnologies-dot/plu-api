<?php

namespace App\Exceptions;

use Throwable;
use App\Exceptions\ApiException;
use Illuminate\Http\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ApiException::class,
    ];
     
    public function register(): void
    {
        $this->renderable(function (ApiException $e, $request) {
            return response([
                'error' => $e->getMessage(),
                'code'  => $e->errorCode, // ✅ MUST MATCH
            ], $e->status);
        });
    }
    
    /*protected function resolveStatus(Throwable $e): int
    {
        if ($e instanceof ApiException) {
            return $e->status;
        }

        return 500; // default
    }

    
   
     

    
    
     
    protected function resolveMessage(Throwable $e): string
    {
        return $e->getMessage() ?: 'Something went wrong';
    }*/
}
