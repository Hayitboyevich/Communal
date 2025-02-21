<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $repository,
        protected FileService             $fileService
    )
    {
    }

    public function create(array $data)
    {
        try {
            $user = $this->repository->create($data);
            if (!empty($data['image'])) {
                $path = $this->fileService->uploadImage($data['image'], 'user/images');
                $user->update(['image' => $path]);
            }
            return $user;

        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
