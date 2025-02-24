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
        return $this->repository->all()->get();
    }

    public function findById(?int $id)
    {
        return $this->repository->findById($id);
    }

    public function createFirst(?array $data)
    {
        return  $this->repository->createFirst($data);
    }

    public function createSecond(?int $id, ?array $data)
    {
        return $this->repository->createSecond($id, $data);
    }

    public function createThird(?int $id, ?array $data)
    {
        return $this->repository->createThird($id, $data);
    }

    public function saveImages(Protocol $protocol, ?array $images)
    {
        foreach ($images as $image) {
            $path = $this->fileService->uploadImage($image, 'protocol/images');
            $protocol->images()->create([
                'url' => $path
            ]);
        }
    }

    public function saveFiles(Protocol $protocol, ?array $files)
    {
        foreach ($files as $file) {
            $path = $this->fileService->uploadImage($file, 'protocol/files');
            $protocol->documents()->create([
                'url' => $path
            ]);
        }
    }
}
