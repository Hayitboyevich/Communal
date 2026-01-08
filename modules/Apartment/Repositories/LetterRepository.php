<?php

namespace Modules\Apartment\Repositories;

use App\Services\EimzoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Modules\Apartment\Contracts\LetterInterface;
use Modules\Apartment\Models\Letter;
use Modules\Apartment\Models\Monitoring;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LetterRepository implements LetterInterface
{
    public function __construct(public Letter $letter, protected EimzoService $imzoService){}

    public function all()
    {
        return $this->letter->query();
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
            $token = $this->authPost();
            $responseData  = $this->sendPost($letter, $token);

            if (!$responseData || !isset($responseData['Id'])) {
                throw new \Exception('Pochtaga yuborishda muammo yuzaga keldi.');
            }

            $letter->update(['letter_id' => $responseData['Id']]);

            $hashCode = $this->getHashCode($letter, $token);

            $letter->update(['letter_hash_code' => $hashCode]);

            DB::commit();
            return $letter;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function change($id, $data)
    {
        DB::beginTransaction();
        try {
            $letter = $this->findById($id);
            $pkcs7b64 = $this->imzoService->signTimestamp($data['signature']);
            $this->sendMail($letter, $pkcs7b64);
            $letter->update(['status' => 2, 'pkcs7' => $pkcs7b64]);
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
        $url = config('apartment.hybrid.url').'/api/mail/'.$id;

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

            return $response->json() ?? null;
        }catch (\Exception $exception){
            throw $exception;
        }

    }

    private function sendPost($letter, $token)
    {
        try {
            $url = config('apartment.hybrid.url').'/api/PdfMail';
            $data = [
                'Receiver' => $letter->fish,
                'Address' => $letter->address,
                'SaotoRegionId' => $letter->region->soato,
                'SaotoAreaId' => $letter->district->soato,
                'Document64' => $this->generatePdf($letter),
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


        $pdf = PDF::loadView('pdf.monitoring', compact('monitoring', 'qrImage'));

        $pdfOutput = $pdf->output();

        return  base64_encode($pdfOutput);
    }



    private function authPost()
    {
        $token = null;
        $url = config('apartment.hybrid.url').'/token';

        $data = [
            'grant_type' => config('apartment.hybrid.grant_type'),
            'username'   => config('apartment.hybrid.username'),
            'password'   => config('apartment.hybrid.password'),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withBody(http_build_query($data), 'application/x-www-form-urlencoded')
            ->get($url);

        if ($response->successful()) {
            $token = $response->json()['access_token'];
        }

        return $token;
    }


}
