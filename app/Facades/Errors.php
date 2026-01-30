<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Errors extends Facade
{
    protected static function getFacadeAccessor()
    {
        // This must match the binding in the service container
        return 'errors';
    }
}
