<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['code' => 'CUS-DEMO-001', 'name' => 'Budi Santoso', 'phone' => '081234560001', 'address' => 'Jl. Merdeka No. 10, Bandung', 'email' => 'budi.santoso@example.test'],
            ['code' => 'CUS-DEMO-002', 'name' => 'Siti Rahmawati', 'phone' => '081234560002', 'address' => 'Jl. Mawar No. 25, Cimahi', 'email' => 'siti.rahmawati@example.test'],
            ['code' => 'CUS-DEMO-003', 'name' => 'Andi Wijaya', 'phone' => '081234560003', 'address' => 'Jl. Sukajadi No. 8, Bandung', 'email' => 'andi.wijaya@example.test'],
            ['code' => 'CUS-DEMO-004', 'name' => 'Dewi Lestari', 'phone' => '081234560004', 'address' => 'Jl. Cibaduyut No. 17, Bandung', 'email' => 'dewi.lestari@example.test'],
            ['code' => 'CUS-DEMO-005', 'name' => 'Rudi Hartono', 'phone' => '081234560005', 'address' => 'Jl. Raya Lembang No. 42, Lembang', 'email' => 'rudi.hartono@example.test'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['code' => $customer['code']],
                $customer + ['registered_at' => now()->toDateString()],
            );
        }
    }
}
