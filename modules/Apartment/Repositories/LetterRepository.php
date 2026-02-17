<?php

namespace Modules\Apartment\Repositories;

use App\Enums\UserRoleEnum;
use App\Services\EimzoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Modules\Apartment\Contracts\LetterInterface;
use Modules\Apartment\Models\Letter;
use Modules\Apartment\Models\Monitoring;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LetterRepository implements LetterInterface
{
    public function __construct(public Letter $letter, protected EimzoService $imzoService){}

    public function all($user, $roleId)
    {
        try {
            switch ($roleId) {
                case UserRoleEnum::APARTMENT_MANAGER->value:
                case UserRoleEnum::REG_VIEWER->value:
                    return $this->letter->query()->where('region_id', $user->region_id);
                case UserRoleEnum::APARTMENT_VIEWER->value:
                    return $this->letter->query();
                default:
                    return $this->letter->query()->whereRaw('1 = 0');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function findById($id)
    {
        return  $this->letter->query()->find($id);
    }

    public function create($data)
    {
        DB::beginTransaction();
        try {
            $letter = $this->letter->create($data);
            $letter->update(['letter_id' => $letter->id]);

            DB::commit();
            return $letter;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function change($id, $signature)
    {
        DB::beginTransaction();
        try {
            $token = $this->authPost();
            $letter = $this->findById($id);
            $pkcs7b64 = $this->imzoService->signTimestamp($signature);
            $responseData  = $this->sendPost($letter, $token);

            if (empty($responseData)) {
                throw new \Exception('Pochtaga yuborishda muammo yuzaga keldi.');
            }

            $letter->update(['status' => 2, 'pkcs7' => $pkcs7b64['pkcs7b64']]);

            DB::commit();
            return $letter;
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }


    public function getLetter($id)
    {
        $token = $this->authPost();
        $url = config('apartment.hybrid.url').'/api/gasn/mail?id='.$id;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get($url);

        return $response->json() ?? null;
    }

    private function getHashCode($letter, $token)
    {
        try {
            $url = config('apartment.hybrid.url').'/api/SendMail/'.$letter->letter_id;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);
            if ($response->successful()) {
                return $response->json();
            }
            return  null;
        }catch (\Exception $exception){
            return null;
        }
    }

    public function sendMail($letter, $signature)
    {
        try {
            $token = $this->authPost();
            $url = config('apartment.hybrid.url').'/api/SendMail/'.$letter->letter_id;
            $data = [
                'signature' => $signature
            ];
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . $token,
            ])->withBody(http_build_query($data), 'application/x-www-form-urlencoded')
                ->put($url);

            if ($response->failed()) throw new \Exception('Hybrid potchada xatolik');

            return $response->json() ?? null;
        }catch (\Exception $exception){
            throw $exception;
        }

    }

    private function sendPost($letter, $token)
    {
        try {
            $url = config('apartment.hybrid.url').'/api/gasn/mail/';
            $data = [
                'Id' => $letter->id,
                'SaotoAreaId' => $letter->district->soato,
                'SaotoRegionId' => $letter->region->soato,
                'ReceiverFullName' => $letter->fish,
                'ReceiverAddress' => $letter->address,
                'Base64Content' => $this->generatePdf($letter),
//                'Receiver' => $letter->fish,
//                'Address' => $letter->address,
//                'SaotoRegionId' => $letter->region->soato,
//                'SaotoAreaId' => $letter->district->soato,
//                'Document64' => $this->generatePdf($letter),
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . $token,
            ])->withBody(http_build_query($data), 'application/x-www-form-urlencoded')
                ->post($url);


            return $response->json() ?? null;

        }catch (\Exception $exception){
            throw $exception;
        }
    }

    private function generatePdf($letter)
    {
        $monitoring = Monitoring::find($letter->monitoring_id);

        $domain = URL::to('/monitoring-pdf') . '/' . $monitoring->id;
        $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));

        $barcode = DNS1D::getBarcodePNG('GASN'.$monitoring->letter->id, 'C39', 2, 40);

        $pdf = PDF::loadView('pdf.letter', compact('monitoring', 'qrImage', 'barcode'));

        $pdfOutput = $pdf->output();

        return  base64_encode($pdfOutput);
    }



    private function authPost()
    {
        $token = null;
        $url = config('apartment.hybrid.url').'/token';

        $data = [
            'grant_type' => config('apartment.hybrid.grant_type'),
            'client_id'   => config('apartment.hybrid.client_id'),
            'client_secret'   => config('apartment.hybrid.client_secret'),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withBody(http_build_query($data), 'application/x-www-form-urlencoded')
            ->post($url);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
        }

        return $token;
    }

    public function receipt($id)
    {
        try {
            $token = $this->authPost();
            $url = config('apartment.hybrid.url').'/gasn/receipt/get?id=GASN'.$id;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);
             return base64_encode($response->body());
        }catch (\Exception $exception){
            throw $exception;
        }
    }

    public function search($query, $filters)
    {
        return $query
            ->when(isset($filters['district_id']), function ($query) use ($filters) {
                $query->where('district_id', $filters['district_id']);
            })
            ->when(isset($filters['region_id']), function ($query) use ($filters) {
                $query->where('region_id', $filters['region_id']);
            })
            ->when(isset($filters['inspector_id']), function ($query) use ($filters) {
                $query->where('inspector_id', $filters['inspector_id']);
            })
            ->when(isset($filters['monitoring_id']), function ($query) use ($filters) {
                $query->where('monitoring_id', $filters['monitoring_id']);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['fish']), function ($query) use ($filters) {
                $query->where('fish', 'like', '%' . $filters['fish'] . '%');
            });
    }


}
