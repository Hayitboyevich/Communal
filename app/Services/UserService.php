<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Http\Requests\UserCreateRequest;
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

    public function create(UserCreateRequest $request)
    {
        DB::beginTransaction();
        try {

            $user = $this->repository->create($request->except(['role_id', 'image']));
            $user->roles()->attach($user->id, ['role_id' => $request->role_id]);

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
