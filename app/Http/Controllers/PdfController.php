<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Apartment\Models\Monitoring;

class PdfController extends Controller
{
    public function monitoringPdf($id)
    {
        try {
            $monitoring = Monitoring::find($id);
            $pdf = PDF::loadView('pdf.monitoring', compact('monitoring'));
            return $pdf->stream('monitoring');
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}
