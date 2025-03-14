<?php
use GuzzleHttp\Client;

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

if (!function_exists('getInfo')) {
    function getInfo(?string $baseUrl, ?string $param = null)
    {
        try {
            $client = new Client();
            $url = $param ? $baseUrl.'='.$param : $baseUrl;

            $resClient = $client->post($url,
                [
                    'headers' => [
                        'client-id' => config('water.card.clientId'),
                        'client-secret' => config('water.card.clientSecret'),
                     ]
                ]);
            $response = json_decode($resClient->getBody(), true);
            return $response['result'];
        }catch (Exception $e){
            return null;
        }
    }
}
