<?php


namespace MrHMH\SmsRahyab;


use Illuminate\Support\Facades\Http;

class RESTRahayabSMS
{

    private $company;
    private $host;
    private $port;
    private $username;
    private $password;
    private $sender;
    private $token;

    public function __construct()
    {
        $this->company = config('services.sms_rahyab.company');
        $this->host = config('services.sms_rahyab.host');
        $this->port = config('services.sms_rahyab.port');
        $this->username = config('services.sms_rahyab.username');
        $this->password = config('services.sms_rahyab.password');
        $this->sender = config('services.sms_rahyab.sender');
        $this->token = config('services.sms_rahyab.token');
    }


    public function sendSms($number, $content)
    {

        Http::withToken($this->token)->post('https://api.rahyab.ir/api/v1/SendSMS_Single', [
            'destinationAddress' => $number,
            'message'            => $content,
            'number'             => $this->sender,
            'userName'           => $this->username,
            'password'           => $this->password,
            'company'            => $this->company
        ]);
    }

}
