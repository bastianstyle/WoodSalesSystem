<?php

use App\Models\User;
use App\Services\Cryptography\InvoiceSignatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admins to view RSA key metadata but not private material', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $key = app(InvoiceSignatureService::class)->generateKeyPair('v-test');

    $response = $this->actingAs($admin)->get(route('admin.keys.index'));

    $response->assertOk()->assertSee('v-test')->assertDontSee($key->encrypted_private_key);
});

it('denies cashiers access to RSA key management', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);

    $this->actingAs($cashier)->get(route('admin.keys.index'))->assertForbidden();
});
