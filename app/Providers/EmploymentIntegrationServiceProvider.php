<?php

namespace App\Providers;

use App\Infrastructure\ExternalApis\EmploymentIntegrationProvider;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class EmploymentIntegrationServiceProvider extends ServiceProvider
{
    private const RETRY_429_MAX = 4;
    // Retry-After bo'lmasa: 5, 10, 20, 40 s. Jami ~75 s, API'ning daqiqalik oynasidan uzunroq
    private const RETRY_429_BASE_DELAY = 5;
    private const RETRY_429_MAX_DELAY = 60;

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EmploymentIntegrationProvider::class, function () {
            $stack = HandlerStack::create();
            // push: http_errors'dan ichkarida turadi, shuning uchun 429 javobni exception'ga aylanmasdan ko'radi
            $stack->push($this->retryOn429(), 'retry_on_429');

            return new EmploymentIntegrationProvider(new Client([
                'handler'         => $stack,
                'timeout'         => 50,
                'connect_timeout' => 10
            ]));
        });
    }

    /**
     * Faqat 'retry_on_429' => true option berilgan requestlar qayta yuboriladi:
     * login kabi foydalanuvchi kutib turgan requestlar bir daqiqa osilib qolmasin.
     */
    private function retryOn429(): callable
    {
        return function (callable $handler) {
            $retry = Middleware::retry(
                fn (int $retries, RequestInterface $request, ?ResponseInterface $response = null) =>
                    $retries < self::RETRY_429_MAX && $response?->getStatusCode() === 429,
                function (int $retries, ?ResponseInterface $response = null) {
                    $seconds = min(
                        $this->retryAfter($response) ?? self::RETRY_429_BASE_DELAY * 2 ** ($retries - 1),
                        self::RETRY_429_MAX_DELAY
                    );

                    Log::info('Egov 429: qayta urinish', ['attempt' => $retries, 'delay_seconds' => $seconds]);

                    return $seconds * 1000;
                }
            )($handler);

            return fn (RequestInterface $request, array $options) => empty($options['retry_on_429'])
                ? $handler($request, $options)
                : $retry($request, $options);
        };
    }

    private function retryAfter(?ResponseInterface $response): ?int
    {
        $value = trim($response?->getHeaderLine('Retry-After') ?? '');

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $date = strtotime($value);

        return $date === false ? null : max($date - time(), 1);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
