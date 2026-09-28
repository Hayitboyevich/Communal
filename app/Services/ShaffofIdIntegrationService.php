<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Infrastructure\ExternalApis\ShaffofIdIntegrationProvider;
use GuzzleHttp\Exception\GuzzleException;

class ShaffofIdIntegrationService
{
    public function __construct(private ShaffofIdIntegrationProvider $provider)
    {
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function getAccessToken(?string $code, string $redirect_uri, string $codeVerify)
    {
        $result = $this->provider->getAccessToken($code, $redirect_uri, $codeVerify);
        $info = base64_decode(explode('.', $result->id_token)[1]);
        return $info;
    }
}
