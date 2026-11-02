<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an admin to create a wood product', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'code' => 'KAYU-001', 'name' => 'Meranti', 'wood_type' => 'Meranti',
        'size' => '4x6 cm', 'length' => 4, 'width' => 6, 'thickness' => 2,
        'unit' => 'batang', 'selling_price' => 125000, 'stock' => 20,
        'minimum_stock' => 5, 'description' => 'Kayu meranti pilihan', 'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    expect(Product::where('code', 'KAYU-001')->exists())->toBeTrue();
});

it('prevents a cashier from deleting a product', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $product = Product::factory()->create();

    $this->actingAs($cashier)->delete(route('admin.products.destroy', $product))
        ->assertForbidden();
});

it('validates product price and stock', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.products.store'), [
        'code' => 'KAYU-002', 'name' => 'Jati', 'wood_type' => 'Jati',
        'unit' => 'batang', 'selling_price' => -1, 'stock' => -1, 'minimum_stock' => 0,
    ])->assertSessionHasErrors(['selling_price', 'stock']);
});
