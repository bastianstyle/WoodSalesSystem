<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProductSeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@sumberrezeki.test'],
            [
                'name' => 'Administrator Demo',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        User::updateOrCreate(
            ['email' => 'kasir@sumberrezeki.test'],
            [
                'name' => 'Kasir Demo',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ],
        );

        $this->call([
            CustomerSeeder::class,
            DemoSalesSeeder::class,
        ]);
    }
}
