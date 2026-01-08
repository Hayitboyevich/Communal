<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;

class InvoiceService
{
    private $token = false;


    public function getToken()
    {
        $client = new Client();
        try {
            if (Cache::has('einvoice_token')) {
                $token = Cache::get('einvoice_token');
            } else {
                $body = [
                    'phone' => config('app.e-invoice.phone'),
                    'password' => config('app.e-invoice.password')
                ];
                $token_expire_time = now()->addDays(30);
                $result = $client->request('POST', config('app.e-invoice.url') . 'login', [
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => "application/json",
                    ],
                    'body' => json_encode($body)
                ])->getBody()->getContents();
                $token = json_decode($result, true)['access_token'];
                Cache::put('einvoice_token', $token, $token_expire_time);
            }
            return $this->token = $token;

        } catch (GuzzleException $e) {
            report($e);
            error_log("E-Invoice login ishlamayapti: " . $e->getMessage());
            Cache::forget('einvoice_token');
            return false;
        }
    }


    public function getCompanyInfo($inn)
    {
        $login = $this->getToken();
        if ($login) {
            $client = new Client();
            try {
                $res = $client->request('get', config('app.e-invoice.url') . 'rouming/company/info?tin='. $inn, [
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => "application/json",
                        'Authorization' => 'Bearer ' . $this->token,
                    ]
                ])->getBody()->getContents();
                $response = json_decode($res, true);
                return $response['data'];
            } catch (GuzzleException $e) {
                report($e);
                error_log("E-Invoice login ishlamayapti: " . $e->getMessage());
                return false;
            }
        }
        return $login;
    }
}
