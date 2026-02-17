<?php

namespace Modules\Apartment\Services;

use App\Services\EimzoService;
use App\Services\InvoiceService;
use Modules\Apartment\Const\LetterStatus;
use Modules\Apartment\Contracts\LetterInterface;
use Modules\Apartment\Http\Requests\LetterRequest;

class LetterService
{
    public function __construct(
        protected LetterInterface $repository,
        protected EimzoService    $imzoService,
        protected InvoiceService  $invoiceService)
    {
    }

    public function getAll($user, $roleId, $filters)
    {
        $query =  $this->repository->all($user, $roleId);
        return $this->repository->search($query, $filters);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function create(LetterRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    public function change($user, $id, $request)
    {
        try {
            $data = $this->imzoService->signTimestamp($request['signature']);
            if (empty($data)) throw new \Exception('E-imzo malumot olishda xatolik yuz berdi');

            $director = $this->checkDirector($data, false);

            if ($director['inn'] != $user->pin) {
                throw new \Exception('E-imzo egasi mos kelmadi');
            }
            return $this->repository->change($id, $request['signature']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

//    public function eimzo($pkcs7, $isYuridik)
//    {
//        $directorPin = null;
//        $fullName = null;
//        $data = $this->imzoService->getUserInfo($pkcs7);
//
//        if (empty($data)) throw new \Exception('Eimzoda ma\'lumot topilmadi');
//
//        if (is_string($data)) throw new \Exception($data);
//
//        if ($isYuridik){
//            if (is_string($data)) throw new \Exception('Ma\'lumot topilmadi '.$data);
//
//            $director = $this->invoiceService->getCompanyInfo($data['identification_number']);
//
//            $directorPin = $director['directorPinfl'];
//            $fullName =  $director['director'];
//
//            if ($data['pin'] != $directorPin) throw new \Exception('Direktor topilmadi');
//        }
//
//        return ['pin' => $directorPin, 'full_name' => $fullName, 'inn' => $data['identification_number']];
//    }

    private function checkDirector($data, $isYuridik)
    {
        $fullName = null;
        if (empty($data['pin'])) {
            throw new \Exception('Foydalanuvchi topilmadi');
        }

        if ($isYuridik) {
            $director = $this->invoiceService->getCompanyInfo(
                $data['inn']
            );

            if (is_string($director)) {
                throw new \Exception('Kompaniya maʼlumoti xato: ' . $director);
            }

            if (!is_array($director)) {
                throw new \Exception('Kompaniya maʼlumoti noto‘g‘ri formatda');
            }

            if (empty($director['directorPinfl']) || empty($director['director'])) {
                throw new \Exception('Direktor maʼlumotlari to‘liq emas');
            }

            $directorPin = $director['directorPinfl'];
            $fullName = $director['director'];
            $inn = $director['tin'];


            if (
                empty($data['pin']) ||
                $data['pin'] != $directorPin
            ) {
                throw new \Exception('Direktor topilmadi yoki E-IMZO mos emas');
            }
        } else {
            $directorPin = $data['pin'];
            $inn = $data['pin'];
        }

        return [
            'pin' => $directorPin,
            'full_name' => $fullName,
            'inn' => $inn,
        ];
    }

    public function getHybrid($id)
    {
        return $this->repository->getLetter($id);
    }

    public function receipt($id)
    {
        try {
            return $this->repository->receipt($id);

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function count($user, $roleId, $filters)
    {
        $query = $this->getAll($user, $roleId, $filters);
        return [
            'All' => $query->clone()->count(),
            'New' => $query->clone()->where('status', LetterStatus::New)->count(),
            'SuccessDelivered' => $query->clone()->where('status', LetterStatus::SuccessDelivered)->count(),
            'ReceiverDead' => $query->clone()->where('status', LetterStatus::ReceiverDead)->count(),
            'Process' => $query->clone()->where('status', LetterStatus::Process)->count(),
            'ReceiverNotLivesThere' => $query->clone()->where('status', LetterStatus::ReceiverNotLivesThere)->count(),
            'IncompleteAddress' => $query->clone()->where('status', LetterStatus::IncompleteAddress)->count(),
            'ReceiverRefuse' => $query->clone()->where('status', LetterStatus::ReceiverRefuse)->count(),
            'NotAtHome' => $query->clone()->where('status', LetterStatus::NotAtHome)->count(),
            'DidntAppearOnNotice' => $query->clone()->where('status', LetterStatus::DidntAppearOnNotice)->count(),
            'Defect' => $query->clone()->where('status', LetterStatus::Defect)->count(),
            'TryPerform' => $query->clone()->where('status', LetterStatus::TryPerform)->count(),
            'OrganizationWithGivenAddressNotFound' => $query->clone()->where('status', LetterStatus::OrganizationWithGivenAddressNotFound)->count(),
        ];
    }

}
