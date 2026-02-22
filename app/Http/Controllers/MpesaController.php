<?php

namespace App\Http\Controllers;

use App\Models\MpesaError;
use App\Models\Mpesamsgs;
use App\Models\MpesaResponse;
use Exception;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
     public function addCallBacks(Request $request)
    {
        try{
            $payload = json_decode($request->getContent());
            if(!property_exists($payload, 'Body')){
                return ['error'=>'no response received'];
            }
            $add = Mpesamsgs::create([
                'message'=>$payload,
                'CheckoutRequestID'=>$payload->Body->stkCallback->CheckoutRequestID
            ]); 
            $merchant_request_id = $payload->Body->stkCallback->MerchantRequestID;
            $checkout_request_id = $payload->Body->stkCallback->CheckoutRequestID;
            $result_desc = $payload->Body->stkCallback->ResultDesc;
            $result_code = $payload->Body->stkCallback->ResultCode;
            $amount = $payload->Body->stkCallback->CallbackMetadata->Item[0]->Value;
            $mpesa_receipt_number = $payload->Body->stkCallback->CallbackMetadata->Item[1]->Value;
            $transaction_date = $payload->Body->stkCallback->CallbackMetadata->Item[3]->Value;
            $phonenumber = $payload->Body->stkCallback->CallbackMetadata->Item[4]->Value;

            if($payload->Body->stkCallback->ResultCode != 0){
                $res = $this->insertMpesaErrors($merchant_request_id, $checkout_request_id, $result_code, $result_desc);
                return ['message'=>'added to errors', 'data'=>$res];
            }

                $data = [
                    'ResponseDescription' => $result_desc,
                    'ResponseCode' => $result_code,
                    'MerchantRequestID' => $merchant_request_id,
                    'CheckoutRequestID' => $checkout_request_id,
                    'amount' => $amount,
                    'MpesaReceiptNumber' => $mpesa_receipt_number,
                    'TransactionDate' => $transaction_date,
                    'PhoneNumber' => $phonenumber,
                ];

                $stkPush = MpesaResponse::where([
                                'MerchantRequestID'=>$merchant_request_id,
                                'CheckoutRequestID'=>$checkout_request_id
                            ])->first();
                if($stkPush){
                    $stkPush->fill($data)->save();
                } else { 
                    MpesaResponse::create($data);
                }
        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return response ($response, 500);
        }
    }

    public function insertMpesaErrors(string $MerchantRequestID, string $CheckoutRequestID, string $ResultCode, string $ResultDesc)
    {
        try{
            $add = MpesaError::create([
                'MerchantRequestID'=>$MerchantRequestID,
                'CheckoutRequestID'=>$CheckoutRequestID,
                'ResponseCode'=>$ResultCode,
                'ResponseDescription'=>$ResultDesc
            ]);
            return $add;

        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return response ($response, 500);
        }
    }

}
