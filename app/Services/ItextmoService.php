<?php

namespace App\Services;

class ItexmoService
{
    protected $email;
    protected $password;
    protected $apiCode;
    protected $endpoint = "https://api.itexmo.com/api/broadcast";

    public function __construct()
    {
        $this->email = env('ITEXMO_EMAIL');
        $this->password = env('ITEXMO_PASSWORD');
        $this->apiCode = env('ITEXMO_APICODE');
    }

    public function sendSms($number, $message)
    {
        $payload = [
            "Email"     => $this->email,
            "Password"  => $this->password,
            "ApiCode"   => $this->apiCode,
            "Recipients"=> [$number], // must be array
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
