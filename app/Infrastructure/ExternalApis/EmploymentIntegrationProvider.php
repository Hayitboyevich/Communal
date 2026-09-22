<?php

namespace App\Infrastructure\ExternalApis;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Traits\HandlesExceptions;
use GuzzleHttp\Client;

class EmploymentIntegrationProvider
{
    use HandlesExceptions;
    private int $get_token = 1;
    private int $current = 2;
    private int $history = 3;

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    private function sendingRequest($method, $url, array $headers_with_body = null)
    {
        $res = match ($method) {
            $this->get_token, $this->current => $this->safeCall(fn() => $this->client->request($method, $url, $headers_with_body)->getBody()->getContents())
        };
        return json_decode($res);
    }

    private function postHeaders(string $token=null, string $pinfl=null)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => "application/json",
        ];
        if ($token) {
            $headers['Authorization'] = $token;
        }
        if ($pinfl) {
            $headers['body'] = ['pin' => $pinfl];
        }
        return $headers;
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getToken()
    {
        $token = config('services.egov.get_token.token');
        $user_name = config('services.egov.get_token.user_name');
        $password = config('services.egov.get_token.password');
        $url = config('services.egov.get_token.url').'?grant_type=password&username='.$user_name.'&password='.$password;
        $headers_with_body = $this->postHeaders(token: $token);
        return $this->sendingRequest(method: 'POST', url: $url, headers_with_body: $headers_with_body);
    }
}
