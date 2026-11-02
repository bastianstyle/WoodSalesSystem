<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Invoice\InvoiceVerificationService;
use Illuminate\Contracts\View\View;

class PublicVerificationController extends Controller
{
    public function show(string $token, InvoiceVerificationService $verification): View
    {
        $invoice = Invoice::with(['digitalSignature.rsaKey', 'sale.customer'])
            ->where('public_token', $token)
            ->firstOrFail();

        $result = $verification->verify($invoice);

        return view('verification.show', [
            'status' => $result['status'],
            'invoice' => $result['invoice'],
        ]);
    }
}
