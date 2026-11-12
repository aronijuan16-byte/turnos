<?php
namespace Database\Seeders;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Trámite documentario','A'],['Licencias','B'],['Pagos y tributos','C'],['Atención social','D']] as [$n,$p])
            Service::firstOrCreate(['prefix' => $p], ['name' => $n]);
    }
}
