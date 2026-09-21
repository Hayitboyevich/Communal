<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Models\UserActionHistory;
use Modules\Water\Services\HistoryService;

class UserService
{
    private HistoryService $historyService;

    public function __construct(
        protected Client                  $client,
        protected UserRepositoryInterface $repository,
        protected FileService             $fileService,
        protected EimzoService            $eimzoService,
    )
    {
        $this->historyService = new HistoryService('user_action_histories');
    }

    public function getAll($user, $roleId, $filters)
    {
        try {
            $query = $this->repository->all($user, $roleId);
            return $this->repository->search($query, $filters);
        } catch (\Exception $exception) {
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

            $user = User::query()->where('pin', $request['pin'])->first();
            $comment = 'Foydalanuvchi qo\'shildi';
            if (!$user) {
                $user = $this->repository->create($request->except(['role_id', 'image', 'images', 'docs']));
                if ($request->hasFile('image')) {
                    $path = $this->fileService->uploadImage($request->file('image'), 'user-history/images');
                    $user->update(['image' => $path]);
                }

                if ($request->images) {
                    $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user-history/images'), $request->images);
                    $user->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
                }
                if ($request->docs) {
                    $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user-history/files'), $request->docs);
                    $user->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
                }
            }

            foreach ($request->role_id as $role) {
                $user->roles()->syncWithoutDetaching([$role]);
            }
            $eimzoSign = $this->eimzoService->signTimestamp($request['pkcs7']);
            if (!in_array(Auth::user()?->pin, Arr::wrap(Arr::get($eimzoSign, 'pin', [])))) {
                return response()->json(['message' => 'Elektron kalit egasi va foydalanuvchi PINFLi mos emas!'], 404);
            }
            $this->createUserActionHistory(user_id: $user->id, comment: $comment, date: now(), status: 1, type: UserActionHistory::TYPE_CREATE, additional_info: $eimzoSign['pkcs7b64']);
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

            $user = $this->repository->update($id, $request->except(['role_id', 'image', 'images', 'docs']));

            $user->roles()->syncWithoutDetaching($request->role_id);
            $comment = 'Foydalanuvchi o\'zgartirildi';

            if (!empty($request->image)) {
                $path = $this->fileService->uploadImage($request->image, 'user/images');
                $user->update(['image' => $path]);
            }

            if (!empty($request->images)) {
                $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user/images'), $request->images);
                $user->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
            }

            if (!empty($request->docs)) {
                $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'user/files'), $request->docs);
                $user->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
            }
            $eimzoSign = $this->eimzoService->signTimestamp($request['pkcs7']);
            if (!in_array(Auth::user()?->pin, Arr::wrap(Arr::get($eimzoSign, 'pin', [])))) {
                return response()->json(['message' => 'Elektron kalit egasi va foydalanuvchi PINFLi mos emas!'], 404);
            }
            $this->createUserActionHistory(user_id: $user->id, comment: $comment, date: now(), status: (int)$request['$request'], type: UserActionHistory::TYPE_UPDATE, additional_info: $eimzoSign['pkcs7b64']);

            DB::commit();
            return $user;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getInfo(string $pin, string $birth_date, ?string $type = null)
    {
        try {
            $url = config('services.passport.url') . '?' . http_build_query([
                    'pinfl' => $pin,
                    'birth_date' => $birth_date,
                    'type' => $type
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

    public function getInspectors($user, $roleId, $filters)
    {
        $query = $this->repository->all($user, $roleId);
        return $this->repository->search($query, $filters);
    }

    public function challenge($pin)
    {
        $user = $this->repository->findByPin($pin);
        if ($user) {
            $data = $this->eimzoService->getChallenge();
            $info['challenge'] = $data;
            $info['roles'] = RoleResource::collection($user->roles);
            return $info;
        }
        return null;
    }

    public function getActionHistory($id)
    {
        try {
            $user = $this->repository->find($id);
            return $user?->actionHistories->map(function ($item) {
                return [
                    'id' => $item->id,
                    'comment' => $item->content->comment,
                    'user' => $item->content->user ? User::query()->find($item->content->user, ['name', 'surname', 'middle_name']) : null,
                    'role' => $item->content->role ? Role::query()->find($item->content->role, ['name', 'description']) : null,
                    'type' => $item->type == UserActionHistory::TYPE_CREATE ? 'create' : ($item->type == UserActionHistory::TYPE_UPDATE ? 'update' : 'delete'),
                    'files' => $item->documents ? DocumentResource::collection($item->documents) : null,
                    'created_at' => $item->created_at,
                ];
            })->sortByDesc('created_at')->values();
            dd($result);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function createUserActionHistory(int $user_id, ?string $comment, ?string $date, int $status, int $type, mixed $additional_info = null): void
    {
        $this->historyService->createHistory(guid: $user_id, status: $status, type: $type, date: $date, comment: $comment, additionalInfo: $additional_info);
    }

}
