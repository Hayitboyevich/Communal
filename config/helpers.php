<?php
use Illuminate\Support\Facades\Http;

if (!function_exists('pagination')) {
    function pagination(object $model)
    {
        if ($model)
            $data = [
                'lastPage' => $model->lastPage(),
                'total' => $model->total(),
                'perPage' => $model->perPage(),
                'currentPage' => $model->currentPage(),
            ];
        else
            $data = [
                'lastPage' => 0,
                'total' => 0,
                'perPage' => 0,
                'currentPage' => 0,
            ];
        return $data;
    }
}

if (!function_exists('postData')) {
    function postData(?string $baseUrl, ?array $data = null)
    {
        try {
            $response = Http::withHeaders([
                'client-id' => config('water.card.clientId'),
                'client-secret' => config('water.card.clientSecret'),
                'Content-TypeList' => 'application/json',
            ])->timeout(10)
            ->post($baseUrl, $data);

            if ($response->successful()) {
                return $response->json() ?? null;
            } else {
                return null;
            }
        }catch (Exception $e){
            return null;
        }
    }
}



if (!function_exists('getUser')) {
    function getUser(?string $baseUrl, ?string $param = null){
        try {
            $response = Http::withBasicAuth(
                config('water.ogoh.login'),
                config('water.ogoh.password')
            )
                ->timeout(10)
                ->post($baseUrl, ['user_id' => $param]);

            if ($response->successful()) {
                return $response->json() ?? null;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

    }
}

if (!function_exists('getData'))
{
    function getData(string $url, ?string $login = null, $password = null, $param = null)
    {
        try {
            $baseUrl = $param ? $url.$param : $url;
            $response = Http::withBasicAuth(
                $login,
                $password
            )
                ->timeout(5)
                ->post($baseUrl);

            if ($response->successful()) {
                return $response->json() ?? null;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('getRegionName')) {
    function getRegionName(?int $regionId){
        $array = [
            1 => 'города Ташкент',
            2 => 'Ташкентской области',
            3 => 'Сырдарьинской области',
            4 => 'Джизакской области',
            5 => 'Самаркандской области',
            6 => 'Ферганской области',
            7 => 'Наманганской области',
            8 => 'Андижанской области',
            9 => 'Кашкадарьинской области',
            10 => 'Сурхандарьинской области',
            11 => 'Бухарской области',
            12 => 'Навоийской области',
            13 => 'Хорезмской области',
            14 => 'Республики Каракалпакстан',
        ];


        return $array[$regionId] ?? null;
    }
}
