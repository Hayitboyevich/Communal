<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Http\Resources\RoleResource;
use App\Infrastructure\ExternalApis\ShaffofIdIntegrationProvider;
use GuzzleHttp\Exception\GuzzleException;

class ShaffofIdIntegrationService
{
    public function __construct(private ShaffofIdIntegrationProvider $provider,
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
        $result['id_token'] = $idToken;
        return $result;
    }

    private
    function base64url_decode(string $data): string
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

    public function refreshSessionCheck($idToken)
    {
        return $this->provider->refreshSession($idToken);
    }
}
