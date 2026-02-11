<?php

namespace App\Http\Controllers\Management;
use App\Services\MpesaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected MpesaService $mpesa;

    public function __construct(MpesaService $mpesa)
    {
        $this->mpesa = $mpesa;
    }

    public function getAccessToken()
    {
        $token = $this->mpesa->getAccessToken();
        return response(['token', $token]);
    }

    public function stkPush()
    {
        $body = $this->mpesa->initiateSTK('254705180969', 1);
        return response($body);
    }
}
