<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $company = Company::factory()->create([
            'name' => 'Servicios Técnicos Demo',
            'slug' => 'servicios-tecnicos-demo',
            'timezone' => 'America/Mexico_City',
        ]);

        User::factory()->forCompany($company)->admin()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.test',
            'locale' => 'es',
        ]);

        User::factory()->forCompany($company)->technician()->create([
            'name' => 'Técnico Demo',
            'email' => 'tecnico@demo.test',
            'locale' => 'es',
        ]);

        User::factory()->unverified()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
