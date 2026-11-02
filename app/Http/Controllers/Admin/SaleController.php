<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\DigitalSignature;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Audit\AuditLogger;
use App\Services\Cryptography\InvoiceSignatureService;
use App\Services\Invoice\CanonicalInvoicePayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        $sales = Sale::with(['customer', 'cashier'])->latest('issued_at')->paginate(10);

        return view('admin.sales.index', compact('sales'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->where('stock', '>', 0)->orderBy('name')->get();

        return view('admin.sales.create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => $products,
            'woodTypes' => $products->pluck('wood_type')->unique()->values(),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $sale = DB::transaction(function () use ($data, $request) {
            $items = [];
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (float) $item['quantity'];
                if ((float) $product->stock < $quantity) {
                    throw ValidationException::withMessages(['items' => "Stok {$product->name} tidak mencukupi."]);
                }
                $lineTotal = round($quantity * (float) $product->selling_price, 2);
                $subtotal += $lineTotal;
                $items[] = compact('product', 'quantity', 'lineTotal');
            }
            $discount = (float) ($data['discount'] ?? 0);
            $grandTotal = round($subtotal - $discount, 2);
            if ($grandTotal < 0 || (float) $data['amount_paid'] < $grandTotal) {
                throw ValidationException::withMessages(['amount_paid' => 'Pembayaran kurang dari total transaksi.']);
            }
            $sale = Sale::create([
                'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'user_id' => $request->user()->id, 'customer_id' => $data['customer_id'],
                'subtotal' => $subtotal, 'discount' => $discount, 'grand_total' => $grandTotal,
                'amount_paid' => $data['amount_paid'], 'change_amount' => round((float) $data['amount_paid'] - $grandTotal, 2),
                'status' => 'issued', 'issued_at' => now(),
            ]);
            foreach ($items as $item) {
                $product = $item['product'];
                $sale->details()->create([
                    'product_id' => $product->id, 'product_code_snapshot' => $product->code,
                    'product_name_snapshot' => $product->name, 'wood_type_snapshot' => $product->wood_type,
                    'size_snapshot' => $product->size, 'unit_snapshot' => $product->unit,
                    'quantity' => $item['quantity'], 'unit_price' => $product->selling_price, 'subtotal' => $item['lineTotal'],
                ]);
                $product->decrement('stock', $item['quantity']);
            }
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
                'issued_at' => now(),
            ]);
            $key = $signatureService->activeKey();
            DigitalSignature::create([
                'invoice_id' => $invoice->id,
                'rsa_key_id' => $key->id,
                'signature_base64' => $signatureService->sign($canonicalJson, $key),
                'hash_algorithm' => 'SHA-256',
                'signature_algorithm' => 'RSA-PSS',
                'payload_hash' => $payloadHash,
                'signed_at' => now(),
            ]);
            app(AuditLogger::class)->record('sale.created', $sale, [
                'invoice_number' => $sale->invoice_number,
                'invoice_id' => $invoice->id,
                'grand_total' => $sale->grand_total,
            ], $request);

            return $sale;
        });

        return redirect()->route('admin.sales.show', $sale)->with('success', 'Transaksi berhasil disimpan.');
    }

    public function show(Sale $sale): View
    {
        $sale->load(['details', 'customer', 'cashier']);

        return view('admin.sales.show', compact('sale'));
    }
}
