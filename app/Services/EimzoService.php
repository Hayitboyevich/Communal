<?php

namespace App\Services;

use App\Enums\UserStatusEnum;
use App\Models\District;
use App\Models\Region;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class EimzoService
{
    private const URL = "http://10.100.1.191:8080";


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
}
