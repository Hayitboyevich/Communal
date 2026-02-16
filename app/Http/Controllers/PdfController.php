<?php

namespace App\Http\Controllers;

use App\Exports\MonitoringExport;
use App\Exports\ProtocolExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Modules\Apartment\Models\Monitoring;
use Modules\Water\Models\Protocol;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfController extends Controller
{

    public function protocolPdf($id)
    {
        try {
            $protocol = Protocol::query()->findOrFail($id);
            $domain = URL::to('/protocol-pdf').'/'.$id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
            $pdf = Pdf::loadView('pdf.protocol', compact(
                'protocol', 'qrImage'
            ));

            return $pdf->download('protocol.pdf');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function monitoringPdf($id)
    {
        try {
            $monitoring = Monitoring::find($id);
            $domain = URL::to('/monitoring-pdf') . '/' . $id;

            $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
            $barcode = DNS1D::getBarcodePNG('GASN'.$monitoring->letter->id, 'C39', 2, 40);
            $pdf = PDF::loadView('pdf.letter', compact('monitoring', 'qrImage', 'barcode'));

            return $pdf->stream('monitoring.pdf');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function monitoringExcel($id)
    {
        try {
            return Excel::download(new MonitoringExport($id), 'monitoring.xlsx');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
    public function protocolExcel($id)
    {
        try {
            return Excel::download(new ProtocolExport($id), 'protocol.xlsx');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }



}
