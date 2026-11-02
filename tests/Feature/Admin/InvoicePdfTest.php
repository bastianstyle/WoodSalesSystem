<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('downloads a generated PDF invoice', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['selling_price' => 100000, 'stock' => 5]);
    $this->actingAs($cashier)->post(route('admin.sales.store'), [
        'customer_id' => $customer->id, 'discount' => 0, 'amount_paid' => 100000,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]);

    $invoice = Invoice::first();
    $response = $this->actingAs($cashier)->get(route('admin.invoices.pdf', $invoice));
    $response->assertOk()->assertHeader('content-type', 'application/pdf');
});
