<?php

namespace Database\Seeders;

use App\Models\User;
<<<<<<< HEAD
=======
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
>>>>>>> 9ca365cbfde83813d92c82223eced04060535030
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
<<<<<<< HEAD
    public function run(): void
    {
        $this->call(ServiceSeeder::class);

        User::firstOrCreate(
            ['email' => 'agente@example.com'],
            ['name' => 'Agente', 'password' => 'password']
        );
=======
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
>>>>>>> 9ca365cbfde83813d92c82223eced04060535030
    }
}
