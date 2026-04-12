<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);

        // Criar usuário admin inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@glux.com.br'],
            [
                'name' => 'Administrador',
                'email' => 'admin@glux.com.br',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        $admin->assignRole('admin');

        $this->command->info('Usuário admin criado: admin@glux.com.br / admin123');
        $this->command->warn('IMPORTANTE: Altere a senha no primeiro acesso!');
    }
}
