<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Http\Requests\UserCreateRequest;
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
        return  $query
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            })
            ->when(isset($filters['full_name']), function ($query) use ($filters) {
                $query->searchByFullName($filters['full_name']);
            })
            ->when(isset($filters['phone']), function ($query) use ($filters) {
                $query->where('phone', $filters['phone']);
            });
    }

}
