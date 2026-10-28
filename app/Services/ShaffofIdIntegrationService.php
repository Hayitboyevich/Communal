<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Http\Resources\RoleResource;
use App\Infrastructure\ExternalApis\ShaffofIdIntegrationProvider;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ShaffofIdIntegrationService
{
    public function __construct(private ShaffofIdIntegrationProvider     $provider,
                                private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public
    function getAccessToken(?string $code, string $redirect_uri, string $codeVerify)
    {
        $result = $this->provider->getAccessToken($code, $redirect_uri, $codeVerify);
        $idToken = $result->id_token;
        $result = explode('.', $idToken);
        $result = json_decode($this->base64url_decode($result[1]), true);
        $pinfl = $result['pinfl'];
        $user = $this->userRepository->findByPin($pinfl);
        if (!$user) throw new NotFoundException('Foydalanuvchi topilmadi');
        $this->userRepository->deleteUserAllIdTokens((int)$user->id);
        $this->userRepository->saveIdToken((int)$user->id, $idToken);
        $result['roles'] = RoleResource::collection($user->roles);
        return $result;
    }

    private function base64url_decode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($data, true);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws ServerException
     */
    public function refreshSession(int $userId)
    {
        $idToken = $this->userRepository->getIdToken($userId);
        if (!$idToken) throw new NotFoundException('Foydalanuvchi topilmadi');
        $this->userRepository->deleteIdToken($userId, $idToken);
        return $this->provider->refreshSession($idToken);
    }

    /**
     * @throws NotFoundException
     */
    public function signDocument(array $data, User $user)
    {
        $token = $this->checkCache('documents');
        return $this->provider->sendSigningDocument(user: $user, accessToken: $token, returnUrl: $data['return_url'], title: $data['title'],
        body: $data['body'], uuid: $data['internal_uuid'], contentType: 'application/json');
    }

    /**
     * @throws LockTimeoutException
     * @throws NotFoundExceptionInterface
     * @throws ServerException
     * @throws ContainerExceptionInterface
     */
    private function checkCache(string $purpose)
    {
        $grantType = match ($purpose) {
            'documents' => 'client_credentials',
            'auth' => 'authorization_code',
            default => throw new ServerException('Unknown grant type'),
        };
        $key = self::key($purpose, $grantType);
        if ($token = cache()->get($key)) {
            return $token;
        }
        return Cache::lock("$key:lock", 10)->block(5, function () use ($purpose, $grantType, $key) {
            if ($token = cache()->get($key)) {
                return $token;
            }
            $result = $this->provider->getAccessToken(grantType: $grantType, accept: 'application/x-www-form-urlencoded',
                scope: $purpose);
            $token = $result->access_token;
            $expiresIn = (int) ($result->expires_in ?? 3600);
            cache()->put($key, $token, max($expiresIn - 60, 60));
            return $token;
        });
    }

    private static function key(string $purpose, string $token): string
    {
        return "ott:$purpose:" . hash('sha256', $token);
    }
}
