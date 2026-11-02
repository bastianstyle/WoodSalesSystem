<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a cashier to create a customer', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);

    $response = $this->actingAs($cashier)->post(route('admin.customers.store'), [
        'code' => 'CUS-001',
        'name' => 'Budi Santoso',
        'phone' => '08123456789',
        'address' => 'Jl. Mawar No. 1',
        'email' => 'budi@example.com',
        'registered_at' => '2026-11-01',
    ]);

    $response->assertRedirect(route('admin.customers.index'));
    expect(Customer::where('code', 'CUS-001')->exists())->toBeTrue();
});

it('prevents duplicate customer codes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Customer::factory()->create(['code' => 'CUS-001']);

    $this->actingAs($admin)->post(route('admin.customers.store'), [
        'code' => 'CUS-001', 'name' => 'Pelanggan Baru', 'phone' => '08123456789',
        'address' => 'Alamat', 'registered_at' => '2026-11-01',
    ])->assertSessionHasErrors('code');
});

it('prevents a cashier from deleting a customer', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $customer = Customer::factory()->create();

    $this->actingAs($cashier)->delete(route('admin.customers.destroy', $customer))
        ->assertForbidden();
});
