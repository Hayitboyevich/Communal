{{--
    Suv protokoli PDF (protocol/pdf/{id}, /protocol-pdf/{id}).
    Yangi shablon: gibrid pochtadagi "Majburiy ko'rsatma" bilan bir xil ko'rinish,
    faqat xat (letter) va barcode ramkasisiz.
--}}
@include('pdf.water_letter', [
    'protocol' => $protocol,
    'qrImage' => $qrImage,
    'letter' => $protocol->letter,
    'barcode' => null,
])
