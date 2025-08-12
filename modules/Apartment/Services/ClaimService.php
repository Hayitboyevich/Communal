<?php

namespace Modules\Apartment\Services;

use App\Services\FileService;
use Modules\Apartment\Contracts\ClaimRepositoryInterface;
use Modules\Apartment\Http\Requests\ClaimRequest;
use Modules\Apartment\Http\Requests\ClaimUpdateRequest;

class ClaimService
{
    public function __construct(
        protected ClaimRepositoryInterface $repository,
        protected FileService $fileService
    ){}

    public function all()
    {
        return $this->repository->all();
    }

    public function findById($id)
    {
        return $this->repository->findById($id);
    }

    public function create(ClaimRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function update($id, ClaimUpdateRequest $request)
    {
        $claim = $this->repository->update($id, $request->except('docs'));
        $this->saveFiles($claim, $request['docs'], 'claim/files');
        return $claim;
    }

    public function count(): array
    {
        try {
            $query = $this->repository->all();
            return [
                'all' => $query->clone()->count(),
                'new' => $query->clone()->where('status', 1)->count(),
                'answered' => $query->clone()->where('status', 2)->count(),
            ];
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    private function saveFiles($model, ?array $files, $filePath)
    {
        if (!empty($files)) {
            $paths = array_map(fn($file) => $this->fileService->uploadImage($file, $filePath), $files);
            $model->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
        }
    }
}
