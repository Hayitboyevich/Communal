<?php

namespace App\Infrastructure\ExternalApis;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Traits\HandlesExceptions;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class EmploymentIntegrationProvider
{
    use HandlesExceptions;

    private int $get_token = 1;
    private int $current = 2;
    private int $history = 3;
    private int $current_pool = 4;

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    private function sendingRequest(int $method_type, string $method, $url, array $headers_with_body = [], array $pool_options = [])
    {
        if ($method_type === $this->current_pool) {
            return $this->poolRequest($method, $url, $pool_options);
        }

        $res = match ($method_type) {
            $this->current => $this->safeCall(fn() => $this->client->request($method, $url, $headers_with_body)->getBody()->getContents()),
        };
        return json_decode($res);
    }

    private function poolRequest(string $method, string $url, array $options): array
    {
        $pinfls      = array_unique($options['pinfls'] ?? []);
        $token       = $options['token'] ?? null;
        $concurrency = $options['concurrency'] ?? 10;
        $acquire     = $options['acquire'] ?? null;
        $results     = [];

        $requests = function () use ($pinfls, $method, $url, $token, $acquire) {
            foreach ($pinfls as $pinfl) {
                yield $pinfl => function () use ($pinfl, $method, $url, $token, $acquire) {
                    $acquire && $acquire();
                    return $this->client->requestAsync(
                        $method,
                        $url,
                        $this->postHeaders(token: $token, pinfl: $pinfl)
                    );
                };
            }
        };

        $pool = new Pool($this->client, $requests(), [
            'concurrency' => $concurrency,
            'fulfilled' => function (ResponseInterface $response, $pinfl) use (&$results) {
                $results[$pinfl] = json_decode($response->getBody()->getContents());
            },
            'rejected' => function ($reason, $pinfl) use (&$results) {
                Log::warning('Egov current work place pool error', [
                    'pinfl' => $pinfl,
                    'error' => $reason instanceof \Throwable ? $reason->getMessage() : (string) $reason,
                ]);
                $results[$pinfl] = null;
            },
        ]);

        $pool->promise()->wait();

        return $results;
    }

    private function postHeaders(string $token = null, string $pinfl = null)
    {
        $headers['headers'] = [
            'Content-Type' => 'application/json',
            'Accept' => "application/json",
        ];
        if ($token) {
            $headers['headers']['Authorization'] = $token;
        }
        if ($pinfl) {
            $headers['body'] = json_encode(['pin' => $pinfl]);
        }
        return $headers;
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    private function getToken()
    {
        $token = config('services.egov.get_token.token');
        $user_name = config('services.egov.get_token.user_name');
        $password = config('services.egov.get_token.password');
        $url = config('services.egov.get_token.url') . '?grant_type=password&username=' . $user_name . '&password=' . $password;
        $headers_with_body = $this->postHeaders(token: $token);
        return $this->sendingRequest(method_type: $this->get_token, method: 'POST', url: $url, headers_with_body: $headers_with_body);
    }

    public function currentWorkPlaceOne(string $pinfl, bool $retryOn429 = false)
    {
        $url = config('services.egov.get_user_info.current_work_place_user_url');
        $token = config('services.api_shaffof_credentials.token');
        $headers_with_body = $this->postHeaders(token: $token, pinfl: $pinfl);
        $headers_with_body['retry_on_429'] = $retryOn429;
        return $this->sendingRequest(method_type: $this->current, method: 'POST', url: $url, headers_with_body: $headers_with_body);
    }

    /**
     * @throws NotFoundException
     * @throws ServerException
     */
    public function currentPoolRequest(array $pinfls, int $concurrency, ?Closure $acquire = null): array
    {
        $url = config('services.egov.get_user_info.current_work_place_user_url');

        return $this->sendingRequest(
            method_type: $this->current_pool,
            method: 'POST',
            url: $url,
            pool_options: [
                'pinfls'      => $pinfls,
                'token'       => config('services.api_shaffof_credentials.token'),
                'concurrency' => $concurrency,
                'acquire'     => $acquire,
            ]
        );
    }
}
