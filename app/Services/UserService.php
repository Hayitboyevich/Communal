<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected Client $client,
        protected UserRepositoryInterface $repository,
        protected FileService             $fileService
    )
    {}

    public function getAll($filters)
    {
        try {
           $query =  $this->repository->all();
           return $this->repository->search($query, $filters);
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


            $user = $this->repository->create($request->except(['role_id', 'image', 'images', 'docs']));

            foreach ($request->role_id as $role) {
                $user->roles()->attach($user->id, ['role_id' => $role]);
            }

            if ($request->hasFile('image')) {
                $path = $this->fileService->uploadImage($request->file('image'), 'user/images');
                $user->update(['image' => $path]);
            }

            if ($request->images) {
                $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user/images'), $request->images);
                $user->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
            }
            if ($request->docs) {
                $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user/files'), $request->docs);
                $user->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
            }

            DB::commit();
            return $user;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function update($id, UserUpdateRequest $request)
    {
        DB::beginTransaction();
        try {

            $user = $this->repository->update($id, $request->except(['role_id', 'image', 'images', 'files']));
            foreach ($request->role_id as $role) {
                $user->roles()->attach($user->id, ['role_id' => $role]);
            }

            if (!empty($request->image)) {
                $path = $this->fileService->uploadImage($request->image, 'user/images');
                $user->update(['image' => $path]);
            }

            if (!empty($request->images)) {
                $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user/images'), $request->images);
                $user->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
            }

            if (!empty($request->files)) {
                $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user/files'), $request->files);
                $user->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
            }

            DB::commit();
            return $user;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getInfo(string $pin, string $birth_date)
    {
        try {
            $url = config('services.passport.url') . '?' . http_build_query([
                    'pinfl' => $pin,
                    'birth_date' => $birth_date
                ]);

            $authHeader = 'Basic ' . base64_encode(
                    config('services.passport.login') . ':' . config('services.passport.password')
                );

            $resClient = $this->client->post($url, [
                'headers' => ['Authorization' => $authHeader]
            ]);

            $response = json_decode($resClient->getBody(), true);

            if (!isset($response['result']['data']['data'][0])) {
                throw new \Exception("Ma'lumot topilmadi");
            }

            $data = $response['result']['data']['data'][0];

            return [
                'pin' => $data['current_pinpp'] ?? null,
                'name' => $data['namelat'] ?? null,
                'surname' => $data['surnamelat'] ?? null,
                'middle_name' => $data['patronymlat'] ?? null,
                'image' => $data['photo'] ?? null,
                'passport_number' => $data['current_document'] ?? null
            ];

        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function getInspectors($filters)
    {
        $query = $this->repository->all()->whereHas('inspectors');
        return  $this->repository->search($query, $filters);
    }

}
