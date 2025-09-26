<?php

namespace App\Services;

class SmsService
{
    protected $endpoint = "https://api.itexmo.com/api/broadcast";
    protected $email;
    protected $password;
    protected $apiCode;

    public function __construct()
    {
        $this->email    = env('ITEXMO_EMAIL');
        $this->password = env('ITEXMO_PASSWORD');
        $this->apiCode  = env('ITEXMO_APICODE');
    }

    public function send(string $number, string $message)
    {
        $payload = [
            "Email"     => $this->email,
            "Password"  => $this->password,
            "ApiCode"   => $this->apiCode,
            "Recipients"=> [$number], 
            "Message"   => $message,
            "SenderId"  => "ITEXMO SMS"
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("{$this->email}:{$this->password}")
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
