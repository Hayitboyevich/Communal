<?php

namespace App\Infrastructure\ExternalApis;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Traits\HandlesExceptions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ShaffofIdIntegrationProvider
{
    use HandlesExceptions;
    private string $accessTokenUrl = 'oauth/token'; //token_id base64 decode qilsak user ma'lumotlari bo'ladi $userInfoUrl'ga request jo'natishga xojat qolmaydi
    private string $userInfoUrl = 'oauth/userinfo';


    public function __construct(private readonly Client $client){}


    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    private function sendRequest($url, $headers_with_body = null)
    {
        $res = match ('POST') {
            $this->accessTokenUrl => $this->safeCall(fn() => $this->client->request('POST',
                config('services.shaffof_id.main_url') . $url, $headers_with_body)->getBody()->getContents()),
            $this->userInfoUrl => $this->safeCall(fn() => $this->client->request('GET', config('services.shaffof_id.main_url') . $url)->getBody()->getContents()),
        };
        return json_decode($res);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getAccessToken(?string $code, string $redirect_uri, string $codeVerify)
    {
        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.shaffofId.id'),
            'client_secret' => config('services.shaffofId.secret'),
            'redirect_uri' => $redirect_uri,
            'code' => $code
        ];

        if ($codeVerify) {
            $payload['code_verifier'] = $codeVerify;
        }
        return $this->sendRequest(url: $this->accessTokenUrl, headers_with_body: $payload);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getInfo(string $accessToken)
    {
        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken
            ]
        ];
        return $this->sendRequest(url: $this->userInfoUrl, headers_with_body: $headers);
    }

    public function refreshSession($tokeId)
    {

    }
}
