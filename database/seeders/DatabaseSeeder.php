<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ServiceSeeder::class);

        User::firstOrCreate(
            ['email' => 'agente@example.com'],
            ['name' => 'Agente', 'password' => 'password']
        );
    }
}
