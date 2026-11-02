<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice): Response
    {
        $invoice->load(['sale.details', 'sale.customer', 'sale.cashier']);
        $verificationUrl = url('/verifikasi/'.$invoice->public_token);
        $qr = new Builder(
            writer: new PngWriter,
            writerOptions: [],
            data: $verificationUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
        );
        $result = $qr->build();
        $pdf = Pdf::loadView('admin.invoices.show', [
            'sale' => $invoice->sale,
            'invoice' => $invoice,
            'qrDataUri' => $result->getDataUri(),
        ])->setPaper('a4');

        return $pdf->download($invoice->sale->invoice_number.'.pdf');
    }
}
