<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $repository,
        protected FileService             $fileService
    )
    {}

    public function getAll()
    {
        try {
           return $this->repository->all();
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function findById($id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $roleId = $data['role_id'];
            unset($data['role_id']);

            $user = $this->repository->create($data);

            $user->roles()->attach($roleId);

            if (!empty($data['image'])) {
                $path = $this->fileService->uploadImage($data['image'], 'user/images');
                $user->update(['image' => $path]);
            }

            DB::commit();
            return $user;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
