<?php

namespace Modules\Apartment\Services;

use App\Services\EimzoService;
use App\Services\InvoiceService;
use Modules\Apartment\Contracts\LetterInterface;
use Modules\Apartment\Http\Requests\LetterRequest;

class LetterService
{
    public function __construct(
        protected LetterInterface $repository,
        protected EimzoService $imzoService,
        protected InvoiceService $invoiceService){}

    public function getAll($user, $roleId)
    {
        return $this->repository->all($user, $roleId);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(LetterRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function change($user, $id, $data)
    {
        try {
            $userInfo = $this->eimzo($data['signature'], false );

            if (!$userInfo) throw new \Exception('Foydalanuvchi topilmadi');

            if ($userInfo['inn'] != $user->pin) {
                throw new \Exception('E-imzo egasi mos kelmadi');
            }
            return $this->repository->change($id, $data);
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function eimzo($pkcs7, $isYuridik)
    {
        $directorPin = null;
        $fullName = null;
        $data = $this->imzoService->getUserInfo($pkcs7);

        if (empty($data)) throw new \Exception('Eimzoda ma\'lumot topilmadi');

        if (is_string($data)) throw new \Exception($data);

        if ($isYuridik){
            if (is_string($data)) throw new \Exception('Ma\'lumot topilmadi '.$data);

            $director = $this->invoiceService->getCompanyInfo($data['identification_number']);

            $directorPin = $director['directorPinfl'];
            $fullName =  $director['director'];

            if ($data['pin'] != $directorPin) throw new \Exception('Direktor topilmadi');
        }

        return ['pin' => $directorPin, 'full_name' => $fullName, 'inn' => $data['identification_number']];
    }

    public function getHybrid($id)
    {
        return $this->repository->getLetter($id);
    }
}
