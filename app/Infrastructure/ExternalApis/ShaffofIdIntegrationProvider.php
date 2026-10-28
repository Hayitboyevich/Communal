<?php

namespace App\Infrastructure\ExternalApis;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Models\User;
use App\Traits\HandlesExceptions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ShaffofIdIntegrationProvider
{
    use HandlesExceptions;

    private string $accessTokenUrl = 'oauth/token'; //token_id base64 decode qilsak user ma'lumotlari bo'ladi $userInfoUrl'ga request jo'natishga xojat qolmaydi
    private string $userInfoUrl = 'oauth/userinfo';
    private string $refreshSessionUrl = 'oauth/logout';
    private string $sendDocumentUrl = 'api/documents';
    private string $checkSigningStatusUrl = 'api/documents/%s';

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws NotFoundException|ServerException
     */
    private function sendRequest($url, $method, $headers_with_body = [])
    {
//        $method = match ($url) {
//            $this->accessTokenUrl, $this->sendDocumentUrl => 'POST',
//            $this->userInfoUrl, $this->refreshSessionUrl, $this->checkSigningStatusUrl => 'GET',
//            default => throw new NotFoundException("Noma'lum URL: {$url}")
//        };
        $res = $this->safeCall(fn() => $this->client->request($method, $url, $headers_with_body)->getBody()->getContents());
        return json_decode($res);
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getAccessToken(?string $grantType, ?string $accept, ?string $codeVerify = null, ?string $code = null, ?string $contentType = null, ?string $scope = null, ?string $redirect_uri = null)
    {
        $headers['form_params'] = [
            'grant_type' => $grantType,
            'client_id' => config('services.shaffof_id.client_id'),
            'client_secret' => config('services.shaffof_id.client_secret')
        ];

        if ($accept) {
            $headers['headers']['Accept'] = $accept;
        }
        if ($contentType) {
            $headers['headers']['Content-Type'] = $contentType;
        }
        if ($code) {
            $headers['form_params']['code'] = $code;
        }
        if ($redirect_uri) {
            $headers['form_params']['redirect_uri'] = $redirect_uri;
        }
        if ($codeVerify) {
            $headers['form_params']['code_verifier'] = $codeVerify;
        }
        if ($scope) {
            $headers['form_params']['scope'] = $scope;
        }

        return $this->sendRequest(url: $this->accessTokenUrl, method: 'POST', headers_with_body: $headers);
    }

    /**
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
        return $this->sendRequest(url: $this->userInfoUrl, method: 'GET', headers_with_body: $headers);
    }

    /**
     * @throws NotFoundException|ServerException
     */
    public function refreshSession($idToken)
    {
        $headers = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' => [
                'client_id' => config('services.shaffof_id.client_id'),
                'id_token_hint' => $idToken,
                'post_logout_redirect_uri' => config('services.shaffof_id.redirect_uri'),
            ],
        ];
        return $this->sendRequest(url: $this->refreshSessionUrl, method: 'GET', headers_with_body: $headers);
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    public function sendSigningDocument(User $user, string $accessToken, ?string $returnUrl, string $title, array $body, string $uuid, ?string $contentType = null)
    {
        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => $contentType,
            ],
            'json' => [
                'title' => "sdfsdfsdf",
                'body' => $body,
                "methods" => ["eimzo"],
                "signer_pinfl" => $user->pin,
                'external_id' => $uuid,
                "callback_url" => "https://test.uz/testable/testback",
                "return_url" => $returnUrl,
                "ttl" => 86400
            ]
        ];
        return $this->sendRequest(url: $this->sendDocumentUrl, method: 'POST', headers_with_body: $headers);
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    public function checkSigningStatus(string $accessToken, string $externalUuid)
    {
        $headers = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken
            ]
        ];
        $url = sprintf($this->checkSigningStatusUrl, $externalUuid);
        return $this->sendRequest(url: $url, method: 'GET', headers_with_body: $headers);
    }
}
