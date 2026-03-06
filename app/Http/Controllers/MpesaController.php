<?php

namespace App\Http\Controllers;

use App\Models\MpesaCallBack;
use App\Models\MpesaError;
use App\Models\Mpesamsgs;
use App\Models\MpesaResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
     public function addCallBacks(Request $request)
    {
        try{
            $payload = json_decode($request->getContent());
            if(!property_exists($payload, 'Body')){
                return ['error'=>'no response received'];
            }
            $merchant_request_id = $payload->Body->stkCallback->MerchantRequestID;
            $checkout_request_id = $payload->Body->stkCallback->CheckoutRequestID;
            $result_desc = $payload->Body->stkCallback->ResultDesc;
            $result_code = $payload->Body->stkCallback->ResultCode;
            if($result_code != 0){
                    //$res = $this->insertMpesaErrors($merchant_request_id, $checkout_request_id, $result_code, $result_desc);
                    $res = MpesaError::create([
                        'MerchantRequestID'=>$merchant_request_id,
                        'CheckoutRequestID'=>$checkout_request_id,
                        'ResultCode'=>$result_code,
                        'ResultDescription'=>$result_desc       
                    ]);
                    return ['message'=>'added to errors', 'data'=>$res];
            }
            $checkout_request_id = $payload->Body->stkCallback->CheckoutRequestID;
            Log::info('checkoutrequestid', ['id'=>$checkout_request_id]);
            Log::info('payload', ['payload'=>$payload]);
            $phonenumber = $payload->Body->stkCallback->CallbackMetadata->Item[4]->Value;
            $number = $this->removeKenyanCountryCode($phonenumber);
            $user = User::where('number', $number)->firstOrFail();
            $add = MpesaCallBack::create([
                'CheckoutRequestID'=>$checkout_request_id ?? null,
                'user_id'=>$user->id ?? null,
                'callback_json'=>$payload,
            ]); 

            
            $amount = 0;
            $mpesa_receipt_number = null;
            $transaction_date = null;
            $phonenumber = null;
            
            if(isset($payload->Body->stkCallback->CallbackMetadata)){
                $amount = $payload->Body->stkCallback->CallbackMetadata->Item[0]->Value;
                $mpesa_receipt_number = $payload->Body->stkCallback->CallbackMetadata->Item[1]->Value;
                $transaction_date = $payload->Body->stkCallback->CallbackMetadata->Item[3]->Value;
                $phonenumber = $payload->Body->stkCallback->CallbackMetadata->Item[4]->Value;
            }
                $data = [
                    //'ResponseDescription' => $result_desc,
                    //'ResponseCode' => $result_code,
                    //'MerchantRequestID' => $merchant_request_id,
                    //'CheckoutRequestID' => $checkout_request_id,
                    'amount' => $amount,
                    'MpesaReceiptNumber' => $mpesa_receipt_number,
                    'TransactionDate' => $transaction_date,
                    'PhoneNumber' => $phonenumber,
                    'status'=>'paid'
                ];
                $stkPush = MpesaResponse::where([
                                'MerchantRequestID'=>$merchant_request_id,
                                'CheckoutRequestID'=>$checkout_request_id
                            ])->first();
                $stkPush->update($data);
                /*$stkPush = MpesaResponse::where([
                                'MerchantRequestID'=>$merchant_request_id,
                                'CheckoutRequestID'=>$checkout_request_id
                            ])->first();
                if($stkPush){
                    $stkPush->fill($data)->save();
                } else { 
                    MpesaResponse::create($data);
                }*/
        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            Log::info('error', $response);
            return response ($response, 500);
        }
    }

    public function removeKenyanCountryCode(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove spaces, dashes, etc.
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 254 and is valid length (12 digits)
        if (preg_match('/^254\d{9}$/', $phone)) {
            return '0' . substr($phone, 3);
        }

        return $phone;
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
