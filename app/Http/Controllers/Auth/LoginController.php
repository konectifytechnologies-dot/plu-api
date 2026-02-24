<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
         public function __invoke()
    {
        request()->validate([
            'number' => ['required', 'string'],
            'password' => ['required'],
        ]);
        $cookieToekn = request()->cookie('XSRF-TOKEN');
        $cookieHeader = request()->header('X-XSRF-TOKEN');
        $sessionid = session()->getId();
        Log::info('xsrf_cookie'.$cookieToekn.'-'.'cookie'.$cookieHeader.'-'.$sessionid);

        /**
         * We are authenticating a request from our frontend.
         */
        if (EnsureFrontendRequestsAreStateful::fromFrontend(request())) {
            $this->authenticateFrontend();
        }
        /**
         * We are authenticating a request from a 3rd party.
         */
        else {
            // Use token authentication.
        }
    }

    
    private function authenticateFrontend()
    {
        if (! Auth::guard('web')
            ->attempt(
                request()->only('number', 'password'),
                request()->boolean('remember')
            )) {
            throw ValidationException::withMessages([
                'number' => __('auth.failed'),
            ]);
        }
    }



}
