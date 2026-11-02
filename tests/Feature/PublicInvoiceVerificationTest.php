<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createIssuedInvoice(): Invoice
{
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['selling_price' => 100000, 'stock' => 5]);

    test()->actingAs($cashier)->post(route('admin.sales.store'), [
        'customer_id' => $customer->id,
        'discount' => 0,
        'amount_paid' => 100000,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]);

    return Invoice::with('digitalSignature.rsaKey')->firstOrFail();
}

it('shows valid when invoice hash and RSA signature match', function () {
    $invoice = createIssuedInvoice();

    $this->get(route('verification.show', $invoice->public_token))
        ->assertOk()
        ->assertSee('Invoice Valid');
});

it('shows invalid when canonical invoice payload is changed', function () {
    $invoice = createIssuedInvoice();
    $invoice->update(['canonical_payload' => ['tampered' => true]]);

    $this->get(route('verification.show', $invoice->public_token))
        ->assertOk()
        ->assertSee('INVALID');
});

it('returns not found for an unknown public token', function () {
    $this->get(route('verification.show', str_repeat('a', 64)))
        ->assertNotFound();
});

it('shows revoked status without exposing invoice details', function () {
    $invoice = createIssuedInvoice();
    $invoice->update(['status' => 'revoked', 'revoked_at' => now()]);

    $this->get(route('verification.show', $invoice->public_token))
        ->assertOk()
        ->assertSee('REVOKED')
        ->assertDontSee($invoice->sale->invoice_number);
});
