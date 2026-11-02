<?php

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records the cashier and transaction activity in the audit log', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['selling_price' => 100000, 'stock' => 5]);

    $this->actingAs($cashier)->post(route('admin.sales.store'), [
        'customer_id' => $customer->id,
        'discount' => 0,
        'amount_paid' => 100000,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
    ]);

    $log = AuditLog::where('action', 'sale.created')->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($cashier->id)
        ->and($log->metadata['invoice_number'])->toStartWith('INV-');
});

it('records RSA key rotation without storing private key material', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.keys.store'), ['version' => 'audit-v1'])
        ->assertSessionHas('success');

    $log = AuditLog::where('action', 'rsa_key.created')->first();
    expect($log)->not->toBeNull()
        ->and($log->metadata['version'])->toBe('audit-v1')
        ->and($log->metadata)->not->toHaveKey('private_key');
});
