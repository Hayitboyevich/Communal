<?php

namespace App\Services;

use App\Enums\UserStatusEnum;
use App\Models\District;
use App\Models\Region;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class EimzoService
{
    private const URL = "http://10.100.1.191:8080";

    public function __construct(protected  InvoiceService $invoiceService, protected Client $client){}

    private function sendingRequest($method, $url, $headers_with_body = null)
    {
        $res = match ($method) {
            'GET' => $this->safeCall(fn() => $this->client->request($method, self::URL . $url)->getBody()->getContents()),
            'POST' => $this->safeCall(fn() => $this->client->request($method, self::URL . $url, $headers_with_body)->getBody()->getContents())
        };
        return json_decode($res);
    }

    private function postHeadrs($pkcs7)
    {
        $user_ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];
        $host = $_SERVER['HTTP_HOST'];
        return [
            'headers' => [
                'Host' => $host,
                'X-Real-IP' => $user_ip
            ],
            'body' => $pkcs7
        ];
    }

    public function infoByPkcs7($pkcs7)
    {
        $headers_with_body = $this->postHeadrs($pkcs7);
        return $this->sendingRequest(method: 'POST', url: '/backend/auth', headers_with_body: $headers_with_body);
    }
    public function signTimestamp($pkcs7)
    {
        $result = $this->getPkcs7($pkcs7);
        $this->checkStatus($result);

        if (empty($result->pkcs7b64)) {
            throw new \Exception('PKCS7 bo‘sh');
        }

        return $result->pkcs7b64;
    }

    public function getPkcs7($pkcs7)
    {
        $headers_with_body = $this->postHeadrs($pkcs7);
        return $this->sendingRequest(method: 'POST', url: '/frontend/timestamp/pkcs7', headers_with_body: $headers_with_body);
    }

    public function attached($pkcs7b64)
    {
        $headers_with_body = $this->postHeadrs($pkcs7b64);
        return $this->sendingRequest(method: 'POST', url: '/backend/pkcs7/verify/attached', headers_with_body: $headers_with_body);
    }


    public function getChallenge(): null|string
    {
        $res = Http::get(self::URL . "/frontend/challenge");

        if ($res->status() != 200) {
            return null;
        }

        $res = $res->object();

        if ($res->status != 1) {
            return null;
        }

        return $res->challenge;
    }

    public function getUserInfo(string $pkcs7)
    {
        $user_ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];
        $host = $_SERVER['HTTP_HOST'];
        $headers = ['Host: ' . $host, 'X-Real-IP: ' . $user_ip];
        $url = self::URL . "/backend/auth";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pkcs7);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            return null;
        }
        $res = json_decode($response, true);

        switch ($res['status']) {
            case 1:
                $userInfo = $res['subjectCertificateInfo']['subjectName'];
                $region = Region::query()->where('name_uz', $userInfo['ST'])->first();
                $inn = isset($userInfo['1.2.860.3.16.1.1']);
                $person_data = [
                    "name" => $userInfo['Name'],
                    "surname" => $userInfo['SURNAME'],
                    "pinfl" => $inn ? $userInfo['1.2.860.3.16.1.1'] : $userInfo['1.2.860.3.16.1.2'],
                    "login" => $inn ? $userInfo['1.2.860.3.16.1.1'] : $userInfo['1.2.860.3.16.1.2'],
                    "password" => Hash::make($userInfo['1.2.860.3.16.1.2']),
                    "active" => UserStatusEnum::ACTIVE->value,
                    "user_status_id" => UserStatusEnum::ACTIVE->value,
                    "organization_name" => $userInfo["O"] ?? null,
                    "region_id" => $region->id,
                ];
                if (isset($userInfo['1.2.860.3.16.1.2']) && !in_array(substr($userInfo['1.2.860.3.16.1.1'] ?? '', 0, 1), ['2', '3'])) {
                    $district = District::query()->where('name_uz', $userInfo['L'])->first();
                    if ($district){
                        $person_data['district_id'] = $district->id;
                    }
                    $identification_number = $userInfo['1.2.860.3.16.1.2'];
                } else {
                    $address = $userInfo["L"];
                    $person_data['address'] = $address;
                    $identification_number = $userInfo['1.2.860.3.16.1.1'];

                }
                return ['person_data' => $person_data, 'identification_number' => $identification_number];
            case -1:
                return "Sertifikat holatini tekshirib bo‘lmadi.";
            case -5:
                return "Imzo vaqti yaroqsiz.";
            case -10:
                return "ERI yaroqsiz.";
            case -11:
                return "Sertifikat haqiqiy emas.";
            case -12:
                return "Sertifikat imzolangan sanada haqiqiy emas.";
            case -20:
                return "ERI topilmadi yoki muddati o‘tgan. Qayta urinib ko'ring.";
            default:
                return "Noma'lum xato. Qayta urinib ko'ring.";
        }
    }

    private function checkStatus($result)
    {
        if (!$result || !isset($result->status)) {
            throw new \Exception('E-imzo servisedan noto‘g‘ri javob keldi');
        }

        if ((int)$result->status !== 1) {
            throw new \Exception($result->message ?? 'E-imzo xatoligi');
        }
    }

    protected function safeCall(callable $callable): mixed
    {
        try {
            return $callable();
        } catch (ModelNotFoundException $e) {
            report($e);
            throw new \Exception('Data not found');
        } catch (\Throwable $exception) {
            report($exception);
            throw new \Exception($exception->getMessage());
        }
    }
}
