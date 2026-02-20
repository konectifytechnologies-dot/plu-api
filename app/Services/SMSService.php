<?php 

namespace App\Services;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class SMSService 
{
    protected string $senderID;
    protected string $password;
    protected string $api_key;
    protected string $sms_api_url;

      public function __construct()
    {
        $this->senderID        = config('sms.senderID');
        $this->password   = config('sms.password');
        $this->api_key= config('sms.api_key');
        $this->sms_api_url = config('sms.sms_api_url');
    }

    public function createSenderId()
    {
        $senderid = 'PLU';
        $postFields = "userid={$this->senderID}&password={$this->password}&senderid={$senderid}&output=json";
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->sms_api_url."senderid/create",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_HTTPHEADER => array(
            //"apiKey: {$this->api_key}",
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ),
        ));

        $response = curl_exec($curl);
        return $response;

    } 

    public function getSenderId()
    {
        $postFields = "userid={$this->senderID}&password={$this->password}&output=json";
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://smsportal.hostpinnacle.co.ke/SMSApi/senderid/read",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded"
        ),
        ));

        $response = curl_exec($curl);
        return $response;
    }

    public function sendSms(string $number)
    {
        $senderid = '4665';
        $postFields = "userid={$this->senderID}&password={$this->password}&&sendMethod=quick&mobile={$number}&msg=Hello World&senderid={$senderid}&msgType=text&output=json";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://smsportal.hostpinnacle.co.ke/SMSApi/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "apikey: {$this->api_key}",
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

	    $response = curl_exec($curl);
        return $response;
    }





}