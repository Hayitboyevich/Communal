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
    private string $refreshSessionUrl = 'oauth/logout';


    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    private function sendRequest($url, $headers_with_body = null)
    {
        $method = match ($url) {
            $this->accessTokenUrl => 'POST',
            $this->userInfoUrl, $this->refreshSessionUrl => 'GET',
            default => throw new NotFoundException("Noma'lum URL: {$url}")
        };
        $res = $this->client->request($method, $url, $headers_with_body)->getBody()->getContents();
        return json_decode($res);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getAccessToken(?string $code, string $redirect_uri, string $codeVerify)
    {
        $headers['headers'] = [
            'Accept' => 'application/json'
        ];
        $headers['form_params'] = [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.shaffof_id.client_id'),
            'client_secret' => config('services.shaffof_id.client_secret'),
            'redirect_uri' => $redirect_uri,
            'code' => $code
        ];

        if ($codeVerify) {
            $headers['form_params']['code_verifier'] = $codeVerify;
        }
        return $this->sendRequest(url: $this->accessTokenUrl, headers_with_body: $headers);
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

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function refreshSession($idToken)
    {
        $headers = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' => [
                'client_id'                => config('services.shaffof_id.client_id'),
                'id_token_hint'            => $idToken,
                'post_logout_redirect_uri' => config('services.shaffof_id.redirect_uri'),
            ],
        ];
        return $this->sendRequest(url: $this->refreshSessionUrl, headers_with_body: $headers);
    }
}
