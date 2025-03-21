<?php

namespace Modules\Water\Services;

use App\Services\FileService;
use Modules\Water\Contracts\ProtocolRepositoryInterface;
use Modules\Water\Models\Protocol;

class ProtocolService
{
    public function __construct(
        protected ProtocolRepositoryInterface $repository,
        protected FileService $fileService
    ){}

    public function getAll()
    {
        return $this->repository->all();
    }

    public function findById(?int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(?array $data)
    {
        return  $this->repository->create($data);
    }

    public function update(?int $id, ?array $data)
    {
        return $this->repository->update($id, $data);
    }


    public function saveImages(Protocol $protocol, ?array $images)
    {
        $paths = array_map(fn($image) => $this->fileService->uploadImage($image, 'protocol/images'), $images);
        $protocol->images()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }

    public function saveFiles(Protocol $protocol, ?array $files)
    {
        $paths = array_map(fn($file) => $this->fileService->uploadImage($file, 'protocol/files'), $files);
        $protocol->documents()->createMany(array_map(fn($path) => ['url' => $path], $paths));
    }
}
