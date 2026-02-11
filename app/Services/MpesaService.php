<?php 

namespace App\Services;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class MpesaService 
{
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortcode;
    protected string $passkey;
    protected string $callbackUrl;

     public function __construct()
    {
        $this->baseUrl        = config('mpesa.base_url');
        $this->consumerKey   = config('mpesa.consumer_key');
        $this->consumerSecret= config('mpesa.consumer_secret');
        $this->shortcode     = config('mpesa.shortcode');
        $this->passkey       = config('mpesa.passkey');
        $this->callbackUrl   = config('mpesa.callback_url');
    }

    public function getAccessToken()
    {
        try{
            $client = new Client();
            $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
            $authToken = base64_encode("{$this->consumerKey}:{$this->consumerSecret}");


            $headers = ['Authorization' => 'Basic ' . $authToken];

            $response = $client->get($url, ['headers' => $headers, 'verify'  => false]);
            
            $responseBody = json_decode($response->getBody(), true);
            $res =  [
                'access_token' => $responseBody['access_token'],
                'expires_in' => $responseBody['expires_in']
            ];
            return $res['access_token'];
        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return response ($response, 500);
        }

    }

    public function initiateSTK(string $number, int $amount=1)
    {
        try{
            
            $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
            $accessToken = $this->getAccessToken();
            $timestamp = Carbon::now('Africa/Nairobi')->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            if(!$accessToken){
                return response(['error'=>'access token not valid', 'code'=>3]);
            }


            $requestHeaders = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ];

            $data = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $number,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $number,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'Rent',
                'TransactionDesc' => 'PLU'
            ];
            $ch = curl_init();

            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $requestHeaders,
                CURLOPT_POSTFIELDS => json_encode($data),

                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            )); 

            $response = curl_exec($ch);

            //Log::channel('mpesa')->info('STK Request initiated');

            if (curl_errno($ch)) {
                $error = 'Error: ' . curl_error($ch);
                curl_close($ch);
                return ['error' => $error];
            }

            curl_close($ch);
            $newMpesaResponse = '';
            $responseBody = json_decode($response, true);
            /*if($responseBody['ResponseCode'] == '0'){
                $newMpesaResponse = Mpesaresponse::create([
                    "MerchantRequestID"=>$responseBody['MerchantRequestID'],
                    "CheckoutRequestID"=>$responseBody['CheckoutRequestID'],
                    "ResponseCode"=>$responseBody['ResponseCode'],
                    "ResponseDescription"=>$responseBody['ResponseDescription'],
                    "CustomerMessage"=>$responseBody['CustomerMessage'],
                ]);
            }*/

            //return json_decode($response, true);

            /*$response = $client->post($url, [
                'headers' => $requestHeaders,
                'json' => $data, // Sends data as JSON
            ]);*/
            return $responseBody;



        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return response ($response, 500);
        }
    }

}