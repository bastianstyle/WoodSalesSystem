<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Services\Cryptography\InvoiceSignatureService;

class InvoiceVerificationService
{
    public function __construct(
        private readonly CanonicalInvoicePayload $canonicalPayload,
        private readonly InvoiceSignatureService $signatureService,
    ) {}

    public function verify(Invoice $invoice): array
    {
        if ($invoice->status === 'revoked') {
            return ['status' => 'REVOKED', 'invoice' => null];
        }

        $signature = $invoice->digitalSignature;
        $key = $signature?->rsaKey;
        if (! $signature || ! $key) {
            return ['status' => 'KEY UNAVAILABLE', 'invoice' => null];
        }

        try {
            $canonicalJson = $this->canonicalPayload->encode($invoice->canonical_payload ?? []);
            $hashMatches = hash_equals($invoice->canonical_payload_hash, hash('sha256', $canonicalJson));
            $signatureMatches = $this->signatureService->verify($canonicalJson, $signature->signature_base64, $key->public_key);
        } catch (\Throwable $exception) {
            report($exception);

            return ['status' => 'INVALID', 'invoice' => null];
        }

        return [
            'status' => $hashMatches && $signatureMatches ? 'VALID' : 'INVALID',
            'invoice' => $hashMatches && $signatureMatches ? $invoice : null,
        ];
    }
}
