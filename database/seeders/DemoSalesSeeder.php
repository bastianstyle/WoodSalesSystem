<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DigitalSignature;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Cryptography\InvoiceSignatureService;
use App\Services\Invoice\CanonicalInvoicePayload;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSalesSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('email', 'kasir@sumberrezeki.test')->firstOrFail();
        $plans = [
            ['invoice' => 'INV-DEMO-001', 'customer' => 'CUS-DEMO-001', 'product' => 'KAYU-JATI-001', 'quantity' => 2, 'discount' => 0],
            ['invoice' => 'INV-DEMO-002', 'customer' => 'CUS-DEMO-002', 'product' => 'KAYU-MERANTI-001', 'quantity' => 4, 'discount' => 10000],
            ['invoice' => 'INV-DEMO-003', 'customer' => 'CUS-DEMO-003', 'product' => 'KAYU-MAHONI-001', 'quantity' => 3, 'discount' => 0],
            ['invoice' => 'INV-DEMO-004', 'customer' => 'CUS-DEMO-004', 'product' => 'KAYU-KAMPER-001', 'quantity' => 2, 'discount' => 15000],
            ['invoice' => 'INV-DEMO-005', 'customer' => 'CUS-DEMO-005', 'product' => 'KAYU-NYATOH-001', 'quantity' => 2, 'discount' => 0],
        ];

        foreach ($plans as $plan) {
            if (Sale::where('invoice_number', $plan['invoice'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($cashier, $plan) {
                $customer = Customer::where('code', $plan['customer'])->firstOrFail();
                $product = Product::where('code', $plan['product'])->lockForUpdate()->firstOrFail();
                $quantity = (float) $plan['quantity'];
                if ((float) $product->stock < $quantity) {
                    throw new \RuntimeException("Stok produk {$product->code} tidak mencukupi untuk data demo.");
                }

                $unitPrice = (float) $product->selling_price;
                $subtotal = round($quantity * $unitPrice, 2);
                $discount = (float) $plan['discount'];
                $grandTotal = round($subtotal - $discount, 2);
                $issuedAt = now();
                $amountPaid = $grandTotal;

                $sale = Sale::create([
                    'invoice_number' => $plan['invoice'],
                    'user_id' => $cashier->id,
                    'customer_id' => $customer->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'grand_total' => $grandTotal,
                    'amount_paid' => $amountPaid,
                    'change_amount' => 0,
                    'status' => 'issued',
                    'issued_at' => $issuedAt,
                ]);

                $sale->details()->create([
                    'product_id' => $product->id,
                    'product_code_snapshot' => $product->code,
                    'product_name_snapshot' => $product->name,
                    'wood_type_snapshot' => $product->wood_type,
                    'size_snapshot' => $product->size,
                    'unit_snapshot' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
                $product->decrement('stock', $quantity);

                $payloadService = app(CanonicalInvoicePayload::class);
                $signatureService = app(InvoiceSignatureService::class);
                $payload = $payloadService->make($sale->fresh(['details', 'customer', 'cashier']));
                $canonicalJson = $payloadService->encode($payload);
                $payloadHash = hash('sha256', $canonicalJson);
                $invoice = Invoice::create([
                    'sale_id' => $sale->id,
                    'public_token' => hash('sha256', Str::random(64).Str::uuid()),
                    'canonical_payload_hash' => $payloadHash,
                    'canonical_payload' => $payload,
                    'status' => 'issued',
                    'issued_at' => $issuedAt,
                ]);
                $key = $signatureService->activeKey();
                DigitalSignature::create([
                    'invoice_id' => $invoice->id,
                    'rsa_key_id' => $key->id,
                    'signature_base64' => $signatureService->sign($canonicalJson, $key),
                    'hash_algorithm' => 'SHA-256',
                    'signature_algorithm' => 'RSA-PSS',
                    'payload_hash' => $payloadHash,
                    'signed_at' => $issuedAt,
                ]);

                app(AuditLogger::class)->record('sale.created', $sale, [
                    'invoice_number' => $sale->invoice_number,
                    'invoice_id' => $invoice->id,
                    'grand_total' => $sale->grand_total,
                    'source' => 'demo-seeder',
                ]);
            });
        }
    }
}
