<?php

namespace App\Services;

use App\Enums\LetterType;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\URL;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Modules\Apartment\Models\Letter;
use Modules\Apartment\Models\Monitoring;
use Modules\Water\Models\Protocol;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LetterPdfService
{
    /**
     * Xat turiga qarab PDF hujjatini yaratadi.
     */
    public function make(Letter $letter): DomPdf
    {
        return match ($letter->letter_type) {
            LetterType::WATER => $this->water($letter),
            default => $this->apartment($letter),
        };
    }

    public function base64(Letter $letter): string
    {
        return base64_encode($this->make($letter)->output());
    }

    private function apartment(Letter $letter): DomPdf
    {
        $monitoring = Monitoring::query()->findOrFail($letter->monitoring_id);

        $domain = URL::to('/monitoring-pdf') . '/' . $monitoring->id;
        $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
        $barcode = DNS1D::getBarcodePNG('GASN' . $letter->id, 'C39', 2, 40);

        return Pdf::loadView('pdf.letter', compact('monitoring', 'qrImage', 'barcode'));
    }

    private function water(Letter $letter): DomPdf
    {
        $protocol = Protocol::query()
            ->with(['region', 'district', 'defect', 'inspector'])
            ->findOrFail($letter->protocol_id);

        $letter->loadMissing('inspector');

        $domain = URL::to('/water-letter-pdf') . '/' . $letter->id;
        $qrImage = base64_encode(QrCode::format('png')->size(200)->generate($domain));
        $barcode = DNS1D::getBarcodePNG('GASN' . $letter->id, 'C39', 2, 40);

        return Pdf::loadView('pdf.water_letter', compact('letter', 'protocol', 'qrImage', 'barcode'))
            ->setPaper('a4', 'portrait');
    }
}
