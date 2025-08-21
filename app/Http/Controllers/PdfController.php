<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\Apartment\Models\Monitoring;
use Modules\Water\Models\Protocol;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfController extends Controller
{
    public function monitoringPdf($id)
    {
        try {
            $monitoring = Monitoring::find($id);
            $domain = URL::to('/regulation-info').'/'.$id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
            $pdf = PDF::loadView('pdf.monitoring', compact('monitoring', 'qrImage'));

            return $pdf->stream('monitoring');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function protocolPdf($id)
    {
        try {
            $protocol = Protocol::find($id);
            $domain = URL::to('/regulation-info').'/'.$id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
            $pdf = PDF::loadView('pdf.protocol', compact('protocol', 'qrImage'));

            return $pdf->stream('protocol');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}
