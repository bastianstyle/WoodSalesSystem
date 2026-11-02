<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a sale and reduces stock atomically', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['selling_price' => 125000, 'stock' => 10]);

    $response = $this->actingAs($cashier)->post(route('admin.sales.store'), [
        'customer_id' => $customer->id,
        'discount' => 25000,
        'amount_paid' => 250000,
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ]);

    $response->assertRedirect();
    $sale = Sale::with('details')->first();
    expect($sale->grand_total)->toBe('225000.00')
        ->and($sale->change_amount)->toBe('25000.00')
        ->and((float) $product->fresh()->stock)->toBe(8.0)
        ->and($sale->details)->toHaveCount(1);
});

it('rejects a sale when stock is insufficient without changing stock', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['stock' => 1]);

    $this->actingAs($cashier)->post(route('admin.sales.store'), [
        'customer_id' => $customer->id, 'discount' => 0, 'amount_paid' => 999999,
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
    ])->assertSessionHasErrors('items');

    expect((float) $product->fresh()->stock)->toBe(1.0)
        ->and(Sale::count())->toBe(0);
});

it('requires payment to cover the grand total', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['selling_price' => 100000, 'stock' => 5]);

    $this->actingAs($cashier)->post(route('admin.sales.store'), [
        'customer_id' => $customer->id, 'discount' => 0, 'amount_paid' => 50000,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ])->assertSessionHasErrors('amount_paid');
});
